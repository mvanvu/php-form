<?php

namespace MaiVu\Php\Form\Field;

class Hidden extends InputAbstract
{
	protected $inputType = 'hidden';

	public function render()
	{
		return $this->toString();
	}
}