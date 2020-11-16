<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Confirm extends Rule
{
	public function validate(Field $field): bool
	{
		if ($form = $field->getForm())
		{
			if (isset($this->params[1]))
			{
				$name  = $this->params[0];
				$value = $this->params[1];

				if ($target = $form->getField($name))
				{
					if (in_array($value, ['', '!']))
					{
						$isEmpty = empty($target->getValue());

						return ('' == $value && $isEmpty) || ('!' == $value && !$isEmpty);
					}

					if (in_array($value, ['x', '!x']))
					{
						$isChecked = $target->get('checked', false);

						return ('x' == $value && $isChecked) || ('!x' == $value && !$isChecked);
					}

					return $target->getValue() == $value;
				}
			}

			if (isset($this->params[0]) && ($target = $form->getField($this->params[0])))
			{
				return $target->getValue() == $field->getValue();
			}
		}

		return false;
	}

	public function dataSetRules(Field $field): array
	{
		if ($form = $field->getForm())
		{
			if (isset($this->params[1]))
			{
				$name  = $this->params[0];
				$value = $this->params[1];

				if ($target = $form->getField($name))
				{
					if (in_array($value, ['', '!', '[-]', '![-]']))
					{
						$op    = $value;
						$value = '';
					}
					else
					{
						$op = '==';
					}

					return [$target->getName(), $op, $value];
				}
			}

			if (isset($this->params[0]) && ($target = $form->getField($this->params[0])))
			{
				return [$field->getName(), '$', '[name^=' . $target->getName() . ']'];
			}
		}

		return [];
	}
}