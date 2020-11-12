<?php

namespace MaiVu\Php\Form;

interface Rule
{
	public function validate(Field $field) : bool;
}
