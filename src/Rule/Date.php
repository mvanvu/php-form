<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;
use DateTime, Exception;

class Date implements Rule
{
	public function validate(Field $field): bool
	{
		$value    = $field->getValue();
		$required = $field->get('required', false);

		if (empty($value) && !$required)
		{
			return true;
		}

		try
		{
			new DateTime($value);
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}
}