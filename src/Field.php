<?php

namespace MaiVu\Php\Form;

use Closure;
use MaiVu\Php\Filter;
use MaiVu\Php\Registry;

abstract class Field
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

	protected $confirmField = null;

	protected $regex = null;

	protected $translate = false;

	protected $ucmFieldId = 0;

	protected $language = '*';

	protected $translationsData = [];

	protected $input = '';

	protected $renderTemplate = null;

	public function __construct($config, Form $form = null)
	{
		if ($form)
		{
			$this->setForm($form);
		}

		$this->load($config);
	}

	public function load($config)
	{
		$config = (new Registry($config))->toArray();

		foreach ($config as $k => $v)
		{
			$this->set($k, $v);
		}

		return $this;
	}

	public function set($attribute, $value)
	{
		if (property_exists($this, $attribute))
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
		}

		return $this;
	}

	public function setTranslate($value)
	{
		$this->translate = boolval($value);

		return $this;
	}

	public function setLanguage($languageCode)
	{
		$this->language = $languageCode;

		return $this;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function setRules(array $rules)
	{
		$this->rules = [];
		$formOptions = Form::getOptions();

		foreach ($rules as $rule)
		{
			$ruleClass = null;

			if (false === strpos($rule, '\\'))
			{
				foreach ($formOptions['ruleNamespaces'] as $namespace)
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
				$ruleObj = new $ruleClass;

				if ($ruleObj instanceof Rule)
				{
					$this->rules[$rule] = $ruleObj;
				}
			}
		}
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
		$defaultMessages     = Form::getOptions()['messages'];
		$value               = $this->getValue();
		$isValid             = true;
		$this->errorMessages = [];
		$placeHolders        = [
			'field' => $this->_($this->label ?: $this->name),
		];

		if ($this->required && ($value != '0' && empty($value)))
		{
			$isValid = false;

			if (isset($this->messages['requireMessage']))
			{
				$this->errorMessages[] = $this->_($this->messages['requireMessage'], $placeHolders);
			}
			else
			{
				$this->errorMessages[] = $this->_($defaultMessages['required'], $placeHolders);
			}
		}

		if (count($this->rules))
		{
			/** @var Rule $ruleHandler */

			foreach ($this->rules as $ruleName => $ruleHandler)
			{
				if (!$ruleHandler->validate($this))
				{
					$isValid = false;

					if (isset($this->messages[$ruleName]))
					{
						$this->errorMessages[] = $this->_($this->messages[$ruleName], $placeHolders);
					}
					else
					{
						$this->errorMessages[] = $this->_($defaultMessages['invalid'], $placeHolders);
					}
				}
			}
		}

		return $isValid;
	}

	public function _(string $text, array $placeHolders = [])
	{
		$renderer = Form::getFieldTranslator();

		if ($renderer instanceof Closure)
		{
			return call_user_func_array($renderer, [$text, $placeHolders]);
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
		$options  = Form::getOptions($options);
		$template = $options['template'];
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


		return $this->loadTemplate(
			$this->renderTemplate,
			[
				'id'          => $this->getId(),
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

	protected function loadTemplate($path, array $displayData = [])
	{
		ob_start();
		include $path;

		return ob_get_clean();
	}

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

		return $this->renderName ?: ($this->form ? $this->form->getRenderFieldName($this->name) : $this->name);
	}

	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	public function getLabel()
	{
		return $this->label;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getErrorMessages()
	{
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

	public function getConfirmField()
	{
		if ($form = $this->getForm())
		{
			return $this->confirmField ? $form->getField($this->confirmField) : false;
		}

		return false;
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
		if (null === $language && is_array($dataValue))
		{
			foreach ($dataValue as $langCode => $value)
			{
				$this->translationsData[$langCode] = $this->cleanValue($value);
			}
		}
		else
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