<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Registry;

class Select extends OptionAbstract
{
	/** @var array */
	protected $options = [];

	/** @var boolean */
	protected $multiple = false;

	public function getValue()
	{
		$value = parent::getValue();

		if ($this->multiple
			&& !is_array($value)
		)
		{
			$value = Registry::parseData($value);
		}

		return $value;
	}

	public function setOptions($options)
	{
		$this->options = Registry::parseData($options);

		return $this;
	}

	public function toString()
	{
		$input = '<select' . ($this->class ? 'class="' . $this->class . '"' : '')
			. ' name="' . $this->getName() . ($this->multiple ? '[]' : '') . '" id="' . $this->getId() . '"'
			. $this->getDataAttributesString();

		if ($this->required)
		{
			$input .= ' required';
		}

		if ($this->readonly)
		{
			$input .= ' readonly';
		}

		if ($this->multiple)
		{
			$input .= ' multiple';
		}

		$input      .= '>';
		$value      = $this->getValue();
		$valueArray = is_array($value) ? $value : [$value];

		foreach ($this->getOptions() as $optKey => $optValue)
		{
			if (is_array($optValue))
			{
				$input .= '<optgroup label="' . $this->renderValue($optKey) . '">';

				foreach ($optValue as $k => $v)
				{
					$selected = in_array((string) $k, $valueArray) ? ' selected' : '';
					$input    .= '<option value="' . $this->renderValue($k) . '"' . $selected . '>' . $this->renderText($v) . '</option>';
				}

				$input .= '</optgroup>';
			}
			else
			{
				$selected = in_array((string) $optKey, $valueArray) ? ' selected' : '';
				$input    .= '<option value="' . $this->renderValue($optKey) . '"' . $selected . '>' . $this->renderText($optValue) . '</option>';
			}
		}

		return $input . '</select>';
	}
}