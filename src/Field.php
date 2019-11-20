<?php

namespace MaiVu\Php\Form;

use MaiVu\Php\Filter;
use MaiVu\Php\Registry;
use MaiVu\Php\Form\Rule\Rule;

abstract class Field
{
	/** @var Form */
	protected $form = null;

	/** @var string */
	protected $group = null;

	/** @var string */
	protected $type = '';

	/** @var string */
	protected $name = '';

	/** @var string */
	protected $renderName = null;

	/** @var string */
	protected $label = '';

	/** @var string */
	protected $description = '';

	/** @var string */
	protected $class = '';

	/** @var string */
	protected $labelClass = '';

	/** @var string */
	protected $id = '';

	/** @var boolean */
	protected $required = false;

	/** @var boolean */
	protected $readonly = false;

	/** @var array */
	protected $dataAttributes = [];

	/** @var array */
	protected $filters = [];

	/** @var array */
	protected $rules = [];

	/** @var array */
	protected $messages = [];

	/** @var array */
	protected $errorMessages = [];

	/** @var mixed */
	protected $value = null;

	/** @var string | null */
	protected $confirmField = null;

	/** @var string | null */
	protected $regex = null;

	abstract public function toString();

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
		$config = Registry::parseData($config);

		foreach ($config as $k => $v)
		{
			$this->set($k, $v);
		}

		return $this;
	}

	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	public function getForm()
	{
		return $this->form;
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

	public function getValue()
	{
		return $this->value;
	}

	public function setValue($value)
	{
		$this->value = $value;

		return $this;
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

	public function setRules(array $rules)
	{
		$this->rules = [];

		foreach ($rules as $rule)
		{
			$ruleClass = 'MaiVu\\Php\\Form\\Rule\\' . $rule;

			if (class_exists($ruleClass))
			{
				$this->rules[$rule] = new $ruleClass;
			}
		}

		return $this;
	}

	public function getRules()
	{
		return $this->rules;
	}

	public function setFilters(array $filters)
	{
		$this->filters = $filters;

		return $this;
	}

	public function getFilters()
	{
		return $this->filters;
	}

	public function cleanValue($value)
	{
		$filters = $this->getFilters();

		if (!empty($value) && count($filters))
		{
			$value = Filter::clean($value, $filters);
		}

		return $value;
	}

	public function applyFilters($value = null)
	{
		if (null === $value)
		{
			$value = $this->getValue();
		}

		// Update value
		$this->setValue($this->cleanValue($value));

		return $value;
	}

	public function getLabel()
	{
		return $this->label ?: $this->name;
	}

	protected function renderMessage($message, $placeHolders = null)
	{
		$keysMaps = [
			'required-field-msg'      => 'The %field% is required',
			'invalid-field-value-msg' => 'The value of %field% is invalid',
		];

		if (isset($keysMaps[$message]))
		{
			$message = $keysMaps[$message];
		}

		if (is_array($placeHolders))
		{
			foreach ($placeHolders as $key => $value)
			{
				$message = str_replace('%' . $key . '%', $value, $message);
			}
		}

		return $message;
	}

	public function isValid()
	{
		$isValid             = true;
		$this->errorMessages = [];
		$value               = $this->getValue();
		$placeHolders        = [
			'field' => $this->getLabel(),
		];

		if ($this->required && ($value != '0' && empty($value)))
		{
			$isValid = false;

			if (isset($this->messages['requireMessage']))
			{
				$this->errorMessages[] = $this->renderMessage($this->messages['requireMessage'], $placeHolders);
			}
			else
			{
				$this->errorMessages[] = $this->renderMessage('required-field-msg', $placeHolders);
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
						$this->errorMessages[] = $this->renderMessage($this->messages[$ruleName], $placeHolders);
					}
					else
					{
						$this->errorMessages[] = $this->renderMessage('invalid-field-value-msg', $placeHolders);
					}
				}
			}
		}

		return $isValid;
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

	public function getConfirmField()
	{
		if ($form = $this->getForm())
		{
			return $this->confirmField ? $form->getField($this->confirmField) : false;
		}

		return false;
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
}