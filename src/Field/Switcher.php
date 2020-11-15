<?php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Assets;

class Switcher extends Check
{
	public function toString()
	{
		static $loadedCss = false;

		if (!$loadedCss)
		{
			$loadedCss = true;
			Assets::addFile(dirname(dirname(__DIR__)) . '/assets/css/switcher.css');
		}

		return '<label class="switch-field">' . parent::toString() . '<span class="slider"></span></label>';
	}
}