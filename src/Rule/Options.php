<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Options implements Rule
{
	public function validate(Field $field): bool
	{
		if (!($options = $field->get('options', [])))
		{
			return false;
		}

		$optionValues = [];
		$value        = $field->getValue();

		foreach ($options as $option)
		{
			if (isset($option['optgroup']))
			{
				foreach ($option['optgroup'] as $opt)
				{
					$optionValues[] = $opt['value'] ?? null;
				}
			}
			else
			{
				$optionValues[] = $option['value'] ?? null;
			}
		}

		if (is_array($value))
		{
			$diff = array_diff($value, $optionValues);

			return empty($diff);
		}

		return in_array((string) $value, $optionValues);
	}
}