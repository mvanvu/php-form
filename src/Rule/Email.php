<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Email extends Rule
{
	public function validate(Field $field): bool
	{
		return false !== filter_var($field->getValue(), FILTER_VALIDATE_EMAIL);
	}
}