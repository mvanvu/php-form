<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Email implements Rule
{
	public function validate(Field $field): bool
	{
		$value    = $field->getValue();
		$required = $field->get('required', false);

		if (empty($value) && !$required)
		{
			return true;
		}

		return false !== filter_var($value, FILTER_VALIDATE_EMAIL);
	}
}