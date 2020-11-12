<?php

namespace MaiVu\Php\Form;

use MaiVu\Php\Registry;

class FormsManager
{
	protected $forms = [];
	protected $messages = [];

	public function set($formName, Form $form)
	{
		$this->forms[$formName] = $form;

		return $this;
	}

	public function has($formName)
	{
		return array_key_exists($formName, $this->forms);
	}

	public function getForms()
	{
		return $this->forms;
	}

	public function remove($formName)
	{
		unset($this->forms[$formName]);

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

	public function bind($data)
	{
		$this->messages = [];
		$isValid        = true;
		$registry       = new Registry;
		$data           = new Registry($data);

		foreach ($this->forms as $form)
		{
			$name    = $form->getName();
			$dataKey = null;

			if (false === strpos($name, '.'))
			{
				$filteredData = $form->bind($data);
			}
			else
			{
				$dataKey      = explode('.', $name, 2)[1];
				$filteredData = $form->bind($data->get($dataKey, []));
			}

			if ($form->isValid())
			{
				if ($dataKey)
				{
					$registry->set($dataKey, $filteredData);
				}
				else
				{
					$registry->merge($filteredData);
				}
			}
			else
			{
				$this->messages = array_merge($this->messages, $form->getMessages());
				$isValid        = false;
			}
		}

		return $isValid ? $registry->toArray() : false;
	}

	public function renderFormFields($formName)
	{
		if ($form = $this->get($formName))
		{
			return $form->renderFields();
		}

		return null;
	}

	/**
	 * @param $formName
	 *
	 * @return Form|null
	 */

	public function get($formName)
	{
		return $this->forms[$formName] ?? null;
	}
}