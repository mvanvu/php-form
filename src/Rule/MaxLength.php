<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class MaxLength extends Rule
{
	public function validate(Field $field): bool
	{
		$length = $this->params[0] ?? -1;

		if ($length)
		{
			$length = (int) $length;
			$value  = $field->getValue();

			if ($field->get('multiple'))
			{
				return is_array($value) && count($value) <= $length;
			}

			return (is_string($value) || is_numeric($value)) && strlen((string) $value) <= $length;
		}

		return false;
	}

	public function dataSetRules(Field $field): array
	{
		return is_numeric($this->params[0]) ? [$field->getId(), '<=', $this->params[0]] : [];
	}
}