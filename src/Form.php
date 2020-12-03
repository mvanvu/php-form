<?php

namespace MaiVu\Php\Form;

use Closure;
use MaiVu\Php\Registry;

class Form
{
	protected static $fieldTranslator = null;
	protected static $options = [
		'fieldNamespaces' => [Field::class],
		'ruleNamespaces'  => [Rule::class],
		'templatePaths'   => [__DIR__ . '/tmpl'],
		'template'        => 'bootstrap',
		'layout'          => 'vertical',
		'messages'        => [
			'required' => '%field% is required!',
			'invalid'  => '%field% is invalid!',
		],
		'languages'       => [
			// ISO code 2 => name
			// 'en' => 'en-GB',
			// 'vn' => 'vi-VN',
		],
	];

	protected $name;

	protected $data;

	protected $fields = [];

	protected $messages = [];

	protected $prefixNameField = '';

	protected $suffixNameField = '';

	protected $beforeValidation = null;

	protected $afterValidation = null;

	public function __construct($name, $data = null)
	{
		if (is_array($name)
			|| is_object($name)
			|| preg_match('/\.(php|json|ini)$/', $name)
		)
		{
			$data = $name;
			$name = '';
		}

		$this->setName($name);
		$this->data = new Registry;

		if ($data)
		{
			$this->load($data);
		}
	}

	public function load($data)
	{
		foreach (Registry::parseData($data) as $config)
		{
			$this->loadField($config);
		}

		return $this;
	}

	protected function loadField($config)
	{
		if (isset($config['type']))
		{
			$fieldClass = null;

			if (false === strpos($config['type'], '\\'))
			{
				foreach (static::$options['fieldNamespaces'] as $namespace)
				{
					if (class_exists($namespace . '\\' . $config['type']))
					{
						$fieldClass = $namespace . '\\' . $config['type'];
						break;
					}
				}
			}
			elseif (class_exists($config['type']))
			{
				$fieldClass = $config['type'];
			}

			if ($fieldClass)
			{
				new $fieldClass($config, $this);
			}
		}
	}

	public static function getFieldTranslator()
	{
		return static::$fieldTranslator;
	}

	public static function setFieldTranslator(Closure $closure)
	{
		static::$fieldTranslator = $closure;
	}

	public static function addFieldNamespaces($namespaces)
	{
		static::setOptions(['fieldNamespaces' => (array) $namespaces]);
	}

	public static function addRuleNamespaces($namespaces)
	{
		static::setOptions(['ruleNamespaces' => (array) $namespaces]);
	}

	public static function addTemplatePaths($paths)
	{
		static::setOptions(['templatePaths' => (array) $paths]);
	}

	public static function setTemplate($template, $layout = 'vertical')
	{
		static::setOptions(['template' => $template, 'layout' => $layout]);
	}

	public function beforeValidation($callback)
	{
		$this->beforeValidation = is_callable($callback) ? $callback : null;
	}

	public function afterValidation($callback)
	{
		$this->afterValidation = is_callable($callback) ? $callback : null;
	}

	public function getRenderFieldName($fieldName, $language = null)
	{
		$i18n      = $this->name ? '[i18n]' : 'i18n';
		$replace   = $language ? $i18n . '[' . $language . ']' : '';
		$subject   = $this->prefixNameField . $fieldName . $this->suffixNameField;
		$fieldName = str_replace('{replace}', $replace, $subject);

		if (!$language && 0 === strpos($fieldName, '['))
		{
			$fieldName = trim($fieldName, '[]');
		}

		return $fieldName;
	}

	public function addField(Field $field)
	{
		$this->fields[$field->getName(true)] = $field;
		$field->setForm($this);

		return $this;
	}

	public function getData($toArray = false)
	{
		return $toArray ? $this->data->toArray() : $this->data;
	}

	public function renderHorizontal()
	{
		return $this->renderFields(['layout' => 'horizontal']);
	}

	public function renderFields(array $options = [])
	{
		$results = [];

		foreach ($this->fields as $field)
		{
			$results[] = $this->renderField($field, $options);
		}

		return implode(PHP_EOL, $results);
	}

	public function renderField($field, $options = [])
	{
		if (!$field instanceof Field)
		{
			$field = $this->getField($field);
		}

		if ($field)
		{
			return $field->render($options);
		}

		return null;
	}

	/**
	 * @param $name
	 *
	 * @return Field | false
	 */

	public function getField($name)
	{
		if (isset($this->fields[$name]))
		{
			return $this->fields[$name];
		}

		return false;
	}

	public function renderTemplate(string $template, bool $horizontal = false)
	{
		$options = ['template' => $template];

		if ($horizontal)
		{
			$options['layout'] = 'horizontal';
		}

		return $this->renderFields($options);
	}

	public function has($fieldName)
	{
		return isset($this->fields[$fieldName]);
	}

	public function count()
	{
		return count($this->fields);
	}

	public function getFields()
	{
		return $this->fields;
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function isValidRequest()
	{
		return $this->isValid($_REQUEST);
	}

	public function isValid($bindData = null, $checkFormName = true)
	{
		$this->messages = [];
		$isValid        = true;

		if (null !== $bindData)
		{
			$this->bind($bindData, $checkFormName);
		}

		if ($this->beforeValidation)
		{
			call_user_func_array($this->beforeValidation, [$this]);
		}

		$this->validateFields($this->fields, $isValid);

		if ($this->afterValidation)
		{
			call_user_func_array($this->afterValidation, [$this, $isValid]);
		}

		return $isValid;
	}

	public function bind($data, $checkFormName = true)
	{
		$languages    = static::getOptions()['languages'];
		$registry     = new Registry($data);
		$filteredData = new Registry;
		$name         = $this->getName();

		if ($checkFormName && $name)
		{
			$registry = new Registry($registry->get($name, []));
		}

		if (count($languages) > 1)
		{
			array_shift($languages);
		}

		foreach ($this->fields as $field)
		{
			$fieldName = $field->getName(true);
			$filteredData->set($fieldName, $field->applyFilters($registry->get($fieldName, null)));

			if ($translateFields = $field->getTranslateFields())
			{
				foreach ($translateFields as $translateField)
				{
					$path = 'i18n.' . $translateField->get('language') . '.' . $fieldName;
					$filteredData->set($path, $translateField->applyFilters($registry->get($path, null)));
				}
			}
		}

		$this->data->merge($filteredData);

		return $filteredData;
	}

	public static function getOptions(array $extendsOptions = [])
	{
		if ($extendsOptions)
		{
			return static::extendsOptions($extendsOptions);
		}

		return static::$options;
	}

	public static function setOptions(array $options)
	{
		static::$options = static::extendsOptions($options);
	}

	public static function extendsOptions(array $options)
	{
		$result = static::$options;

		foreach ($options as $name => $value)
		{
			if (isset($result[$name]) && gettype($value) === gettype($result[$name]))
			{
				if (is_array($value))
				{
					foreach (array_reverse($value) as $k => $v)
					{
						if (is_integer($k))
						{
							array_unshift($result[$name], $v);
						}
						else
						{
							$result[$name][$k] = $v;
						}
					}
				}
				else
				{
					$result[$name] = $value;
				}
			}
		}

		return $result;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName(string $name)
	{
		if (strpos($name, '.'))
		{
			$parts  = explode('.', $name);
			$prefix = array_shift($parts) . '{replace}';
			$count  = count($parts);

			if ($count > 1)
			{
				$prefix .= '[' . implode('][', $parts) . ']';
			}
			elseif ($count === 1)
			{
				$prefix .= '[' . $parts[0] . ']';
			}

			$this->prefixNameField = $prefix . '[';
			$this->suffixNameField = ']';
		}
		else
		{
			$this->prefixNameField = $name . '{replace}[';
			$this->suffixNameField = ']';
		}

		$this->name = $name;
	}

	protected function validateFields($fields, &$isValid)
	{
		/** @var Field $field */
		foreach ($fields as $field)
		{
			if ($field->isValid())
			{
				// Update field data
				$this->data->set($field->getName(true), $field->getValue());
			}
			else
			{
				$isValid        = false;
				$this->messages = array_merge($this->messages, $field->getErrorMessages(false));
			}

			if ($translateFields = $field->getTranslateFields())
			{
				$this->validateFields($translateFields, $isValid);
			}
		}
	}

	public function remove($fieldName)
	{
		if (isset($this->fields[$fieldName]))
		{
			unset($this->fields[$fieldName]);
		}

		return $this;
	}
}