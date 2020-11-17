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

	public function dataSetRules(Field $field): array
	{
		$regex = '^[a-zA-Z0-9.!#$%&\'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$';

		return [$field->getId(), '#', $regex];
	}
}