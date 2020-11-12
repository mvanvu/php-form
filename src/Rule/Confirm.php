<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Confirm implements Rule
{
	public function validate(Field $field): bool
	{
		if ($confirmField = $field->getConfirmField())
		{
			return $field->getValue() === $confirmField->getValue();
		}

		return false;
	}
}