<?php

namespace MaiVu\Php\Form;

use MaiVu\Php\Registry;

abstract class Rule
{
	protected static $aliases = [
		'-'  => 'Confirm',
		'@'  => 'Email',
		'>=' => 'MinLength',
		'<=' => 'MaxLength',
		'#'  => 'Regex',
	];

	protected $params;

	public function __construct($params = [])
	{
		$this->params = new Registry($params);
	}

	public static function setRuleAlias($name, $alias)
	{
		static::$aliases[$alias] = $name;
	}

	public static function getRuleAliases()
	{
		return static::$aliases;
	}

	public function dataSetRules(Field $field): array
	{
		return [];
	}

	abstract public function validate(Field $field): bool;
}
