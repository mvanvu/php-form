<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Confirm extends Rule
{
	public function validate(Field $field): bool
	{
		if (!($form = $field->getForm()))
		{
			return false;
		}

		if ($params = $this->params->toArray())
		{
			$key   = array_keys($params)[0];
			$value = $params[$key];

			if (is_string($key) && ($confirmField = $form->getField($key)))
			{
				return $confirmField->getValue() == $params[$key];
			}

			if ($confirmField = $form->getField($value))
			{
				return $confirmField->getValue() == $field->getValue();
			}
		}

		return false;
	}
}