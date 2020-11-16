<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class MinLength extends Rule
{
	public function validate(Field $field): bool
	{
		return is_numeric($this->params[0]) && strlen((string) $field->getValue()) >= (int) $this->params[0];
	}

	public function dataSetRules(Field $field): array
	{
		return is_numeric($this->params[0]) ? [$field->getName(), '>=', $this->params[0]] : [];
	}
}