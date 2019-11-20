<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Registry;

abstract class OptionAbstract extends Field
{
	/** @var array */
	protected $options = [];

	public function setOptions($options)
	{
		$this->options = Registry::parseData($options);

		return $this;
	}

	public function getOptions()
	{
		return $this->options;
	}

	protected function renderValue($value)
	{
		return htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8');
	}

	protected function renderText($text)
	{
		return htmlentities((string) $text);
	}
}