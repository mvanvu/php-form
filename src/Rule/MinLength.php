<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class MinLength extends Rule
{
	public function validate(Field $field): bool
	{
		$minLength = $this->params[0];

		if (is_numeric($minLength))
		{
			return strlen((string) $field->getValue()) >= (int) $minLength;
		}

		return false;
	}
}