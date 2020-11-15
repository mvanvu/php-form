<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Regex extends Rule
{
	public function validate(Field $field): bool
	{
		$regex = $field->get('regex', null);

		return 1 === @preg_match('/' . $regex . '/', $field->getValue());
	}
}