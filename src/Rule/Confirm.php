<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;

class Confirm implements Rule
{
	public function validate(Field $field)
	{
		if ($confirmField = $field->getConfirmField())
		{
			return $field->getValue() === $confirmField->getValue();
		}

		return false;
	}
}