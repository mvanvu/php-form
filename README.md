# Php Form Package
## Installation via Composer (not yet)
```json
{
	"require": {
		"mvanvu/php-form": "~1.0"
	}
}
```

Alternatively, from the command line:

```sh
composer require mvanvu/php-form
```
## Usage

``` php
use MaiVu\Php\Form\Form;

// Data is an array or mixed that will be parsed by Registry see more (https://github.com/mvanvu/php-registry)
$data = [
	[
		'name'          => 'checkField',
		'type'          => 'Check',
		'checkboxValue' => 'Y',
		'value'         => 'Y',
		'filters'       => ['yesNo'],
	],
	[
		'name'     => 'textField',
		'type'     => 'Text',
		'value'    => 'Default text value',
		'hint'     => 'Text placeholder',
		'required' => true,
		'rules'    => ['Email'],
		'messages' => [
			'requireMessage' => 'The textField is required.',
			'Email'          => 'Invalid email.',
		],
	],
	[
		'name'    => 'selectField',
		'type'    => 'Select',
		'value'   => 'optValue1',
		'options' => [
			'foo'   => 'bar',
			'value' => 'text',
			'Group' => [
				'optValue1' => 'optText 1',
				'optValue2' => 'optText 2',
			],
		],
		'rules'   => ['Options'],
	],
];

// Create a form and initialize fields data
$form = new Form('FormData', $data);

// Or
$form = new Form('FormData');
$form->load($data);

// Render fields
echo $form->renderFields();

// Output
<input name="FormData[checkField]" type="checkbox" id="FormData-checkField" value="Y" checked/>
<input name="FormData[textField]" type="text" id="FormData-textField" value="Default text value" required placeholder="Text placeholder"/>
<select name="FormData[selectField]" id="FormData-selectField">
    <option value="foo">bar</option>
    <option value="value">text</option>
    <optgroup label="Group">
        <option value="optValue1" selected>optText 1</option>
        <option value="optValue2">optText 2</option>
    </optgroup>
</select>

// Render by a specific field name
echo $form->renderField('textField');

// Output
<input name="FormData[textField]" type="text" id="FormData-textField" value="Default text value" required placeholder="Text placeholder"/>

// Get a field object
$field = $form->getField('textField');
var_dump($field); // Dump
echo $field->toString(); // Render field
echo $field->getValue(); // Get field value
$field->setValue($value) // Set field value
...

// Bind data (array|mixed)
$form->bind(
    [
        'textField' => 'Update text field value',
    ]
);

echo $form->renderField('textField');

// Output
<input name="FormData[textField]" type="text" id="FormData-textField" value="Update text field value" required placeholder="Text placeholder"/>
```

## Use Rules and filters see more (https://github.com/mvanvu/php-filter)
```php
$data = [
    [
        ...
        'filters' => ['string', 'trim'],
        'rules' => ['Options'], // case sensitive
        'messages' => [
            'Options' => 'Invalid option value.',
        ],
    ],
];

$postData = [
    'selectField' => 'No option value',
];

$validData = $form->bind($postData);

// Return false
if ($form->isValid())
{
    $validData = $form->getData()->toArray();
}
else
{
    // return ['Invalid option value.'];
    var_dump($form->getMessages());
}


// The same
if ($form->isValid($postData))
{
    $validData = $form->getData()->toArray();
}
else
{
    // return ['Invalid option value.'];
    var_dump($form->getMessages());
}

```

## With form or without form
#### With form
```php
echo $form->renderField('textField');

// Output the name field which includes the form name (FormData) => FormData[textField]
<input name="FormData[textField]" type="text" id="FormData-textField" value="Update text field value" required placeholder="Text placeholder"/>

// Deep form name
$form = new Form('FormData.params', $fieldsData);
echo $form->renderField('textField'); // field name is FormData[params][textField]

$form = new Form('FormData.foo.bar', $fieldsData);
echo $form->renderField('textField'); // field name is FormData[foo][bar][textField]
```

#### Without form
```php

use MaiVu\Php\Form\Field\Text;

$textField = new Text(
	[
		'name'     => 'textField',
		'type'     => 'Text',
		'value'    => 'Default text value',
		'hint'     => 'Text placeholder',
		'required' => true,
		'rules'    => ['Email'],
		'messages' => [
			'requireMessage' => 'The textField is required.',
			'Email'          => 'Invalid email.',
		],
	]
);

echo $textField->toString();

// Output the name field which does not include the form name (FormData) => textField
<input name="textField" type="text" id="textField" value="Default text value" required placeholder="Text placeholder"/>

```

## Create a new field
```php

// Create a new field at src/Field/Custom.php

namespace MaiVu\Php\Form\Field;

use MaiVu\Php\Form\Field;

class Custom extends Field
{
    public function toString()
    {
        return 'This is my custom field with name is: ' . $this->name;
    }
}

// Use
$form = new Form('FormData', 
    [
        [
            'name' => 'myCustomField',
            'type' => 'Custom',
            ...
        ]
    ]
);

// Return 'This is my custom field with name is myCustomField: '
echo $form->renderField('myCustomField');
```

## Default fields see at path src/Field

* Check
* Email
* Hidden
* Number
* Password
* Radio
* Select
* Text
* TextArea