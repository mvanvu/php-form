<?php

namespace MaiVu\Php\Form\Rule;

use MaiVu\Php\Form\Field;
use MaiVu\Php\Form\Rule;

class Confirm extends Rule
{
	/** @var Field */
	protected $field;
	protected $value;
	protected $op = '==';
	protected $isValid = false;

	public function validate(Field $field): bool
	{
		if ($this->parseParams($field))
		{
			$value = $this->field->getValue();

			if ($this->isValid)
			{
				// Only validate this when $this->when == $value
				return true;
			}

			switch ($this->op)
			{
				case 'in':
				case '!in':

					if (is_array($value))
					{
						$result = true;

						foreach ($value as $val)
						{
							if (!in_array($val, $this->value))
							{
								$result = false;
								break;
							}
						}
					}
					else
					{
						$result = null !== $value && in_array($value, $this->value);
					}

					return 'in' === $this->op ? $result : !$result;

				case '==':
				case '!':
					$result = (array) $value === (array) $this->value;

					return '==' === $this->op ? $result : !$result;
			}
		}

		return false;
	}

	protected function parseParams(Field $field)
	{
		if ($form = $field->getForm())
		{
			if (isset($this->params[0]))
			{
				if (isset($this->params[1]))
				{
					$this->field = $form->getField($this->params[0]);
					$this->value = $this->params[1];
				}
				else
				{
					$this->field = $field;
					$this->value = $this->params[0];
				}

				if ($this->field)
				{
					if (preg_match('/^(in|!in|!)?\s*\[(.+)\]$/i', $this->value, $matches))
					{
						$this->op    = $matches[1] ?: '==';
						$this->value = explode(',', $matches[2]);
					}
					elseif (preg_match('/^(!?)(.*)$/i', $this->value, $matches))
					{
						$this->op    = $matches[1] ?: '==';
						$this->value = $matches[2];
					}

					$whenRegex = '/\[when:(.*)\]$/i';

					if (preg_match($whenRegex, $this->value, $matches))
					{
						$this->isValid = ($matches[1] ?: '') != $field->getValue();
					}

					$this->value = preg_replace($whenRegex, '', $this->value);

					return true;
				}
			}
		}

		return false;
	}
}