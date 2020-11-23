<?php

namespace MaiVu\Php\Form;

use MaiVu\Php\Registry;

class FormsManager
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

	public function has($name)
	{
		return array_key_exists($name, $this->forms);
	}

	public function getForms()
	{
		return $this->forms;
	}

	public function remove($name)
	{
		unset($this->forms[$name]);

		return $this;
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

	public function isValid($data, $checkFormName = true): bool
	{
		$isValid        = true;
		$this->messages = [];

		foreach ($this->forms as $form)
		{
			$filteredData = $form->bind($data, $checkFormName);

			if ($form->isValid())
			{
				$this->data->merge($filteredData);
			}
			else
			{
				$isValid        = false;
				$this->messages = array_merge($this->messages, $form->getMessages());
			}
		}

		return $isValid;
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
}