<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class MaxLength extends Rule
{
	public function validate(Field $field): bool
	{
		$maxLength = $this->params[0];

		if (is_numeric($maxLength))
		{
			return strlen((string) $field->getValue()) <= (int) $maxLength;
		}

		return false;
	}
}