<?php

namespace MaiVu\Php\Form\Field\Base;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Registry;

abstract class OptionsBase extends Field
{
	protected $options = [];

	public function getOptions()
	{
		return $this->options;
	}

	public function setOptions($options)
	{
		$this->options = Registry::parseData($options);

		return $this;
	}
}