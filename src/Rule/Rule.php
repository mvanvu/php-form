<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;

interface Rule
{
	public function validate(Field $field);
}
