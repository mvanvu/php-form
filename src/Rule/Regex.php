<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Regex implements Rule
{
	public function validate(Field $field): bool
	{
		$value    = $field->getValue();
		$regex    = $field->get('regex', null);
		$required = $field->get('required', false);

		if (empty($regex) || (empty($value) && $value != '0' && !$required))
		{
			return true;
		}

		return 1 === @preg_match('/' . $regex . '/', $value);
	}
}