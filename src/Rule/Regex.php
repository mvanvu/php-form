<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Regex extends Rule
{
	public function validate(Field $field): bool
	{
		$regex = $this->params[0] ?? null;

		return $regex && 1 === @preg_match('/' . $regex . '/', $field->getValue());
	}

	public function dataSetRules(Field $field): array
	{
		$regex = $this->params[0] ?? null;

		return $regex ? [$field->getName(), '#', $regex] : [];
	}
}