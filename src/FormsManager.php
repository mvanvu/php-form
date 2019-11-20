<?php

namespace MaiVu\Php\Form;

class FormsManager
{
	protected $forms = [];

	/**
	 * @param $formName
	 *
	 * @return Form
	 */

	public function get($formName)
	{
		return $this->forms[$formName];
	}

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
		if (isset($this->forms[$formName]))
		{
			unset ($this->forms[$formName]);
		}

		return $this;
	}

	public function count()
	{
		return count($this->forms);
	}
}