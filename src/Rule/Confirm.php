<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Confirm extends Rule
{
	public function validate(Field $field): bool
	{
		$fieldName = null;
		$value     = $field->getValue();

		if ($params = $this->params->toArray())
		{
			$key   = array_keys($params)[0];
			$value = $params[$key];

			if (is_string($key))
			{
				$fieldName = $key;
			}
		}

		if ($confirmField = $field->getConfirmField($fieldName))
		{
			return $confirmField->getValue() == $value;
		}

		return false;
	}
}