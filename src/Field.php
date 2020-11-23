<?php

namespace MaiVu\Php\Form;

use ArrayAccess;
use Closure;
use MaiVu\Php\Assets;
use MaiVu\Php\Filter;
use MaiVu\Php\Registry;

abstract class Field implements ArrayAccess
{
	protected $form = null;

	protected $group = null;

	protected $type = '';

	protected $name = '';

	protected $renderName = null;

	protected $label = '';

	protected $description = '';

	protected $class = '';

	protected $id = '';

	protected $required = false;

	protected $readonly = false;

	protected $disabled = false;

	protected $dataAttributes = [];

	protected $filters = [];

	protected $rules = [];

	protected $messages = [];

	protected $errorMessages = [];

	protected $showOn = '';

	protected $value = null;

	protected $translate = false;

	protected $language = null;

	protected $translationsData = [];

	protected $input = '';

	protected $renderTemplate = null;

	protected $originConfigData = [];

	public function __construct($config, Form $form = null)
	{
		$this->load($config);

		if ($form)
		{
			$form->addField($this);
		}
	}

	public function load($config)
	{
		$this->originConfigData = array_merge(
			[
				'name'           => null,
				'label'          => null,
				'required'       => false,
				'dataAttributes' => [],
				'messages'       => [],
				'rules'          => [],
			],
			Registry::parseData($config)
		);

		foreach ($this->originConfigData as $k => $v)
		{
			$this->set($k, $v);
		}

		return $this;
	}

	public function set($attribute, $value)
	{
		$excludes = ['originConfigData'];

		if (!in_array($attribute, $excludes) && property_exists($this, $attribute))
		{
			$method = 'set' . ucfirst($attribute);

			if (method_exists($this, $method))
			{
				$this->{$method}($value);
			}
			else
			{
				$this->{$attribute} = $value;
			}

			$this->originConfigData[$attribute] = $this->{$attribute};
		}

		return $this;
	}

	public function setTranslate($value)
	{
		$this->translate = boolval($value);

		return $this;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function setRules(array $rules)
	{
		$this->rules = [];

		foreach ($rules as $name => $rule)
		{
			$this->setRule($name, $rule);
		}

		return $this;
	}

	public function setRule($name, $rule)
	{
		if ($rule instanceof Closure)
		{
			$this->rules[$name] = $rule;

			return $this;
		}

		$ruleClass = null;
		$rawName   = $rule;
		$params    = [];

		if (false !== strpos($rawName, ':'))
		{
			list($rule, $params) = explode(':', $rawName, 2);
			$tmp = [];

			foreach (explode('|', $params) as $param)
			{
				if (false === strpos($param, '='))
				{
					$tmp[] = $param;
				}
				else
				{
					list($k, $v) = explode('=', $param, 2);
					$tmp[$k] = $v;
				}
			}

			$params = $tmp;
		}

		if (false === strpos($rule, '\\'))
		{
			foreach (Form::getOptions()['ruleNamespaces'] as $namespace)
			{
				if (class_exists($namespace . '\\' . $rule))
				{
					$ruleClass = $namespace . '\\' . $rule;
					break;
				}
			}
		}
		elseif (class_exists($rule))
		{
			$ruleClass = $rule;
		}

		if ($ruleClass)
		{
			$ruleObj = new $ruleClass($params);

			if ($ruleObj instanceof Rule)
			{
				$this->rules[$rawName] = $ruleObj;
			}
		}

		return $this;
	}

	public function setDataAttributes($value)
	{
		$this->dataAttributes = array_merge($this->dataAttributes, (array) $value);

		return $this;
	}

	public function applyFilters($value = null, $forceNull = false)
	{
		if (null === $value && !$forceNull)
		{
			// Default value
			$value = $this->getValue();
		}

		// Update value
		$this->setValue($this->cleanValue($value));

		// Always use $this->getValue() callback to get the value of this field
		return $this->getValue();
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	public function cleanValue($value)
	{
		if ($filters = $this->getFilters())
		{
			$value = Filter::clean($value, $filters);
		}

		return $value;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;

		return $this;
	}

	public function isValid()
	{
		$value               = $this->getValue();
		$isValid             = true;
		$this->errorMessages = [];

		if ($this->required && ($value != '0' && empty($value)))
		{
			$isValid               = false;
			$this->errorMessages[] = $this->getRuleMessage('required');
		}

		if (count($this->rules))
		{
			/** @var Rule | Closure $ruleHandler */

			foreach ($this->rules as $ruleName => $ruleHandler)
			{
				if ($ruleHandler instanceof Closure)
				{
					$result = call_user_func_array($ruleHandler, [$this]);

					if (!is_bool($result))
					{
						$result = false;
					}
				}
				else
				{
					$result = $ruleHandler->validate($this);
				}

				if (!$result)
				{
					$isValid               = false;
					$this->errorMessages[] = $this->getRuleMessage($ruleName);
				}
			}
		}

		if (!$isValid && $this->errorMessages)
		{
			Registry::session()->set('phpFormFieldMessage.' . $this->name, $this->errorMessages);
		}

		return $isValid;
	}

	protected function getRuleMessage($ruleName)
	{
		$default      = Form::getOptions()['messages'];
		$placeHolders = [
			'field' => $this->_($this->label ?: $this->name),
		];

		if (isset($this->messages[$ruleName]))
		{
			return $this->_($this->messages[$ruleName], $placeHolders);
		}

		return $this->_($default[$ruleName] ?? $default['invalid'], $placeHolders);
	}

	public function _(string $text, array $placeHolders = [])
	{
		if (($translator = Form::getFieldTranslator()) instanceof Closure)
		{
			return call_user_func_array($translator, [$text, $placeHolders]);
		}

		if ($placeHolders)
		{
			foreach ($placeHolders as $name => $value)
			{
				$text = str_replace('%' . $name . '%', $value, $text);
			}
		}

		return $text;
	}

	public function render($options = [])
	{
		static $paths = [];
		$options   = Form::getOptions($options);
		$template  = $options['template'];
		$languages = $options['languages'];

		if (!isset($paths[$template]))
		{
			// Default template is Bootstrap v4
			$paths[$template] = __DIR__ . '/tmpl/bootstrap';

			foreach ($options['templatePaths'] as $path)
			{
				$path .= '/' . $template . '/renderField.php';

				if (is_file($path))
				{
					$paths[$template] = $path;
					break;
				}
			}
		}

		$this->renderTemplate = $paths[$template];
		$this->input          = $this->toString();
		$id                   = $this->getId();

		if ($this->get('translate') && count($languages) > 1)
		{
			Assets::addFiles(
				[
					__DIR__ . '/../assets/css/i18n.css',
					__DIR__ . '/../assets/js/i18n.js',
				]
			);

			$tabTitles   = ['<li class="active"><a href="#' . $id . '">' . $this->convertLanguageToFlag(array_keys($languages)[0]) . '</a></li>'];
			$tabContents = ['<li class="active" id="' . $id . '">' . $this->input . '</li>'];
			array_shift($languages);

			foreach ($languages as $code2 => $language)
			{
				$configData = array_merge(
					$this->get('originConfigData'),
					[
						'value'          => $this->translationsData[$language] ?? null,
						'renderTemplate' => $this->renderTemplate,
						'language'       => $language,
					]
				);

				$translate     = new static($configData, $this->form);
				$tabTitles[]   = '<li><a href="#' . $translate->getId() . '">' . $this->convertLanguageToFlag($code2) . '</a></li>';
				$tabContents[] = '<li id="' . $translate->getId() . '">' . $translate->toString() . '</li>';
			}

			$this->input = '<div class="php-form-translation-field"><ul>' . implode($tabContents) . '</ul><ul>' . implode($tabTitles) . '</ul></div>';
		}

		return $this->loadTemplate(
			$this->renderTemplate,
			[
				'id'          => $id,
				'label'       => trim($this->getLabel()),
				'description' => trim($this->getDescription()),
				'errors'      => $this->getErrorMessages(),
				'horizontal'  => $options['layout'] === 'horizontal',
				'showOn'      => $this->getShowOn(),
				'class'       => 'field-' . $this->getType(),
				'required'    => $this->get('required'),
			]
		);
	}


	abstract public function toString();

	public function getId()
	{
		if (empty($this->id))
		{
			$this->setId($this->getName());
		}

		return $this->id;
	}

	public function setId($id)
	{
		$this->id = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $id);
		$this->id = trim($this->id, '-_');

		return $this->id;
	}

	public function getName($rawName = false)
	{
		if ($rawName)
		{
			return $this->name;
		}

		return $this->renderName ?: ($this->form ? $this->form->getRenderFieldName($this->name, $this->language) : $this->name);
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function get($attribute, $defaultValue = null)
	{
		if (property_exists($this, $attribute))
		{
			$method = 'get' . ucfirst($attribute);

			if (method_exists($this, $method))
			{
				return $this->{$method}($attribute);
			}

			return $this->{$attribute};
		}

		return $defaultValue;
	}

	public function convertLanguageToFlag($language)
	{
		$langCode2 = substr($language, 0, 2);

		if (function_exists('mb_convert_encoding'))
		{
			$flag = '';

			foreach (str_split(strtoupper($langCode2)) as $char)
			{
				$stringRegional = ord($char) + 127397;
				$flag           .= mb_convert_encoding('&#' . $stringRegional . ';', 'UTF-8', 'HTML-ENTITIES');
			}

			return $flag;
		}

		return $language;
	}

	protected function loadTemplate($path, array $displayData = [])
	{
		ob_start();
		include $path;

		return ob_get_clean();
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getErrorMessages($flash = true)
	{
		if ($flash)
		{
			return Registry::session()->getFlash('phpFormFieldMessage.' . $this->name, $this->errorMessages);
		}

		return $this->errorMessages;
	}

	public function getShowOn()
	{
		$showOnData = [];

		if (empty($this->showOn))
		{
			return $showOnData;
		}

		$formName    = $this->form->getName();
		$showOnParts = preg_split('/(\||\&)/', $this->showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
		$op          = '';

		foreach ($showOnParts as $showOnPart)
		{
			if ('|' === $showOnPart || '&' === $showOnPart)
			{
				$op = $showOnPart;
				continue;
			}

			list ($fieldName, $value) = explode(':', $showOnPart, 2);

			if ($this->form)
			{
				if (false === strpos($fieldName, '.'))
				{
					$fieldName = $this->form->getRenderFieldName($fieldName);
				}
				else
				{
					$parts       = explode('.', $fieldName);
					$fieldName   = array_pop($parts);
					$tmpFormName = implode('.', $parts);

					if ($tmpFormName === $formName)
					{
						$fieldName = $this->form->getRenderFieldName($fieldName);
					}
					else
					{
						$tmpForm   = new Form($tmpFormName);
						$fieldName = $tmpForm->getRenderFieldName($fieldName);
						unset($tmpForm);
					}
				}
			}

			$showOnData[] = [
				'op'    => $op,
				'field' => $fieldName,
				'value' => $value,
			];

			if ('' !== $op)
			{
				$op = '';
			}
		}

		if ($showOnData)
		{
			Assets::addFile(dirname(__DIR__) . '/assets/js/show-on.js');
		}

		return $showOnData;
	}

	public function setShowOn($showOnData)
	{
		$this->showOn = str_replace(' & ', '&', trim($showOnData));
		$this->showOn = str_replace(' | ', '|', $this->showOn);
		$this->showOn = str_replace(' : ', ':', $this->showOn);

		return $this;
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		$this->type = ucfirst($type);

		return $this;
	}

	public function getForm(): ?Form
	{
		return $this->form;
	}

	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	public function setTranslationData($dataValue, $language = null)
	{
		if (is_array($dataValue))
		{
			foreach ($dataValue as $langCode => $value)
			{
				if (is_array($value) && array_key_exists($this->name, $value))
				{
					$this->translationsData[$langCode] = $this->cleanValue($value[$this->name]);
				}
			}
		}
		elseif ($language)
		{
			$this->translationsData[$language] = $this->cleanValue($dataValue);
		}

		return $this;
	}

	public function applyTranslationValue($language)
	{
		$this->setValue($this->getTranslationData($language));

		return $this;
	}

	public function getTranslationData($language = null)
	{
		if (null === $language)
		{
			return $this->translationsData;
		}

		return isset($this->translationsData[$language]) ? $this->translationsData[$language] : null;
	}

	public function __get($name)
	{
		return $this->get($name);
	}

	public function __set($name, $value)
	{
		return $this->set($name, $value);
	}

	public function __toString()
	{
		return $this->toString();
	}

	public function offsetExists($offset)
	{
		return property_exists($this, $offset);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		return $this->set($offset, $value);
	}

	public function offsetUnset($offset)
	{
		return $this->set($offset, null);
	}

	public function setMessage($name, $value)
	{
		$this->messages[$name] = (string) $value;

		return $this;
	}

	protected function getDataAttributesString()
	{
		$dataAttributes = '';

		if ($this->dataAttributes)
		{
			foreach ($this->dataAttributes as $dataKey => $dataValue)
			{
				if (is_array($dataValue) || is_object($dataValue))
				{
					$dataValue = json_encode($dataValue);
				}

				$dataAttributes .= ' data-' . $dataKey . '="' . htmlspecialchars((string) $dataValue, ENT_COMPAT, 'UTF-8') . '"';
			}
		}

		return $dataAttributes;
	}

	protected function renderValue($value)
	{
		return htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8');
	}

	protected function renderText($text)
	{
		return htmlentities($this->_((string) $text));
	}
}