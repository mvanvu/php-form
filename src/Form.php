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
	];

	protected $name;

	protected $data;

	protected $fields = [];

	protected $messages = [];

	protected $prefixNameField = '';

	protected $suffixNameField = '';

	public function __construct($name, $data = null, $rootKey = null)
	{
		$this->name = $name;

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

		$this->data = new Registry;

		if ($data)
		{
			$this->load($data, $rootKey);
		}
	}

	public function load($data, $rootKey = null)
	{
		$data = Registry::parseData($data);

		if ($rootKey && isset($data[$rootKey]))
		{
			$data = $data[$rootKey];
		}

		foreach ($data as $config)
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
				$field = new $fieldClass($config, $this);

				if ($field instanceof Field)
				{
					$name                = $field->getName(true);
					$this->fields[$name] = $field;
				}
			}
		}
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
				$result[$name] = is_array($value) ? array_merge($value, $result[$name]) : $value;
			}
		}

		return $result;
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

	public function getRenderFieldName($fieldName, $language = null)
	{
		$replace   = $language ? '[translations][' . $language . ']' : '';
		$subject   = $this->prefixNameField . $fieldName . $this->suffixNameField;
		$fieldName = str_replace('{replace}', $replace, $subject);

		if (0 === strpos($fieldName, '['))
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

	public function renderField($name, $options = [])
	{
		if ($field = $this->getField($name))
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

	public function getData($toArray = false)
	{
		return $toArray ? $this->data->toArray() : $this->data;
	}

	public function getName()
	{
		return $this->name;
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
			$results[] = $field->render($options);
		}

		return implode(PHP_EOL, $results);
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

	public function isValid($bindData = null)
	{
		$this->messages = [];
		$isValid        = true;

		if (null !== $bindData)
		{
			$this->bind($bindData);
		}

		/** @var Field $field */
		foreach ($this->fields as $field)
		{
			if ($field->isValid())
			{
				// Update field data
				$this->data->set($field->getName(true), $field->getValue());
			}
			else
			{
				$isValid        = false;
				$this->messages = array_merge($this->messages, $field->get('errorMessages', []));
			}
		}

		return $isValid;
	}

	public function bind($data, array $translationsData = [])
	{
		$registry     = new Registry($data);
		$filteredData = [];

		foreach ($this->fields as $field)
		{
			$fieldName                = $field->getName(true);
			$filteredData[$fieldName] = $field->applyFilters($registry->get($fieldName, null));

			if (isset($translationsData[$fieldName]))
			{
				$field->setTranslationData($translationsData[$fieldName]);
			}
		}

		$this->data->merge($filteredData);

		return $filteredData;
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