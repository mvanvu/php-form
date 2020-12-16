<?php

namespace MaiVu\Php\Form;

use ArrayAccess;
use MaiVu\Php\Registry;

class FormsManager implements ArrayAccess
{
	protected $forms = [];
	protected $messages = [];
	protected $data;

	public function __construct(array $forms = [])
	{
		$this->data = new Registry;

		if ($forms)
		{
			foreach ($forms as $name => $form)
			{
				if (is_integer($name))
				{
					$this->add($form);
				}
				else
				{
					$this->set($name, $form);
				}
			}
		}
	}

	public function add(Form $form)
	{
		$this->forms[] = $form;

		return $this;
	}

	public function set($name, Form $form)
	{
		$this->forms[$name] = $form;

		return $this;
	}

	public function getForms()
	{
		return $this->forms;
	}

	public function count()
	{
		return count($this->forms);
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function isValidRequest()
	{
		return $this->isValid($_REQUEST);
	}

	public function isValid($bindData = null, $checkFormName = true): bool
	{
		if (null !== $bindData)
		{
			$this->bind($bindData, $checkFormName);
		}

		$isValid        = true;
		$this->messages = [];

		foreach ($this->forms as $form)
		{
			if (!$form->isValid())
			{
				$isValid        = false;
				$this->messages = array_merge($this->messages, $form->getMessages());
			}
		}

		return $isValid;
	}

	public function bind($data, $checkFormName = true): array
	{
		foreach ($this->forms as $form)
		{
			$formData = $form->bind($data, $checkFormName);

			if ($name = $form->getName())
			{
				$this->data->set($name, $formData);
			}
			else
			{
				$this->data->merge($formData);
			}
		}

		return $this->data->toArray();
	}

	public function renderHorizontal($name, array $options = [])
	{
		$options['layout'] = 'horizontal';

		return $this->renderFormFields($name, $options);
	}

	public function renderFormFields($name, array $options = [])
	{
		if ($form = $this->get($name))
		{
			return $form->renderFields($options);
		}

		return null;
	}

	public function get($name): ?Form
	{
		return $this->forms[$name] ?? null;
	}

	public function getData($asArray = false)
	{
		return $asArray ? $this->data->toArray() : $this->data;
	}

	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	public function has($name)
	{
		return array_key_exists($name, $this->forms);
	}

	public function offsetUnset($offset)
	{
		return $this->remove($offset);
	}

	public function remove($name)
	{
		unset($this->forms[$name]);

		return $this;
	}

	public function __get($name)
	{
		return $this->offsetGet($name);
	}

	public function __set($name, $value)
	{
		return $this->offsetSet($name, $value);
	}

	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	public function offsetSet($offset, $value)
	{
		if ($value instanceof Form)
		{
			$this->forms[$offset] = $value;
		}

		return $this;
	}
}