# Php Form Package
Manage the form fields in easy way. 

## Features

* Render form via templates: Bootstrap (v3 and v4) and Uikit v3
* Ability to add new custom fields
* Ability to add new custom rules (for validation)
* Cool feature show/hide on
* Create once and using to render HTML form and validate from the PHP server


## Include dependencies
* Php-assets (see https://github.com/mvanvu/php-assets)
* Php-registry (see https://github.com/mvanvu/php-registry)
* Php-filters (see https://github.com/mvanvu/php-filters)


## Installation via Composer
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

## Testing

1 - Clone this repo:

`   git clone https://github.com/mvanvu/php-form.git    
`

2 - Go to the repo

`
    cd php-from
`

3 - Composer install

`
    composer install
`

4 - Run test server

`
php -S localhost:9000/tests
`

5 - Open the browser with url localhost:9000/tests

## Usage

```php
use MaiVu\Php\Form\Form;

$fieldsData = [/*See tests/index.php to know how to create the fields data*/];
$form = new Form($fieldsData);

// Default template is Bootstrap (the both v3 and v4 are working)
echo $form->renderFields();

// Render Uikit 3 template
echo $form->renderTemplate('uikit-3');

// Or set default template
Form::setTemplate('uikit-3');
echo $form->renderFields();

// Render horizontal
echo $form->renderHorizontal();

// Validate form
if ($form->isValid($_POST))
{
    echo 'Cool! insert the valid data to the database';
    $data      = $form->getData(); // Instance of Registry
    $validData = $data->toArray();
    var_dump($validData);

}
else
{
    echo 'Oops. The form is invalid:<br/>' . implode('<br/>', $form->getMessages());
}

```

## Default fields see at path src/Field

* Switcher (must include assets/css/switcher.css if you don't use the php-assets)
* Check
* CheckList
* Email
* Hidden
* Number
* Password
* Radio
* Select
* Text
* TextArea

## Show on feature
Show or hide the base field in the conditions (UI likes the Joomla! CMS Form)

``` php

    use MaiVu\Php\Form\Form;
    
    $form = new Form(
        [
            [
                'name'     => 'pass1',
                'type'     => 'Password',
                'label'    => 'Password',
                'class'    => 'form-control',
                'required' => true,
            ],
            [
                'name'     => 'pass2',
                'type'     => 'Password',
                'label'    => 'Confirm password',
                'class'    => 'form-control',
                'required' => true,
                'rules'    => ['Confirm:pass1', 'Confirm:pass1|2468'],
                'messages' => [
            	    'Confirm:pass1'      => 'Password is not match!',
            	    'Confirm:pass1|2468' => 'Password must be: 2468',
                ],
                'showOn'   => 'pass1 : is not empty',
            ],
        ]
    );

    // Before render field we must include assets/js/show-on.js
    // OR render before HTML </body> closed tag by using php-assets):
    // use MaiVu\Php\Assets;
    // Assets::compress();
    // Assets::output('js');

    echo $form->renderFields();

```

## Show on values
### Formats: {fieldName} = the name of field

* {fieldName} : is checked (Show when the {fieldName} is checked)
* {fieldName} : is not checked (Show when the {fieldName} is not checked)
* {fieldName} : is selected (Show when the {fieldName} is selected)
* {fieldName} : is not selected (Show when the {fieldName} is not checked)
* {fieldName} : is empty (Show when the {fieldName} is empty)
* {fieldName} : is not empty (Show when the {fieldName} is not empty)
* {fieldName} : abc123 (Show when the {fieldName} has value == abc123)
* {fieldName} : !abc123 (Show when the {fieldName} has value != abc123)

### AND Operator (&)
* {fieldName} : is not empty & {fieldName} : abc123

### OR Operator (|)
* {fieldName} : is not empty | {fieldName} : abc123

## Filters
This is A Php Filters native. Just use the filters attributes (String or Array) like the Php Filters (see https://github.com/mvanvu/php-filters) 

## Default Validations (see at path src/Rule)
### Confirm
```php
    $password1 = [/** Password1 config data */];
    $password2 = [
        'name'    => 'password2',
        'type'    => 'Text',
        'label'   => 'My Field Label',
        'filters' => ['basicHtml', 'trim'],
        'rules'   => [
            'Confirm:password1', // password2 must be match with password1,
            'Confirm:password1:12345' // Password 2 will be valid when password1 is 12345
        ],
        'messages' => [
            'Confirm:password1'       => 'The password is not match!',
            'Confirm:password1:12345' => 'The password 1 must be 12345!',
        ],
    ];
    
```

### Email
```php    
    // Just use the Email type
    $email = [
        'name'     => 'Email',
        'type'     => 'Email',
        'label'    => 'My Email',
        'messages' => [
            'Email' => 'Invalid email.'
        ],
    ];

    // OR set its rules contain Email: 'rules' => ['Email']    
```

### Date
Check the value is a valid date

### MinLength and MaxLength
```php        
    $text = [
        'name'     => 'MyField',
        'type'     => 'TextArea',
        'label'    => 'My Field',
        'rules'    => ['MinLength:5', 'MaxLength:15'],
        'messages' => [
            'MinLength:5'  => 'Minimum is 5 chars.',
            'MaxLength:15' => 'Maximum is 15 chars.'
        ],
    ];    
```

### Options 
```php     
    // Invalid if the value is not in the options attributes  
    $select = [
        'name'     => 'MyField',
        'type'     => 'Select',
        'label'    => 'My Field',
        'options'  => [
            [
                'value' => '1',
                'text'  => 'Yes',
            ],
            [
                'value' => '0',
                'text'  => 'No',
            ],
        ],
        'rules'    => ['Options'],
        'messages' => [
            'Options' => 'The value not found.', // Invalid if the value is not (1 or 0)           
        ],
    ];    
```

### Regex 
```php   
    $regex = [
        'name'     => 'MyField',
        'type'     => 'TextArea',
        'label'    => 'My Field',        
        'rules'    => ['Regex'],
        'regex'    => '[0-9]+',
        'messages' => [
            'Regex' => 'The value must be an unsigned number',
        ],
    ];    
```

### Custom function
```php   
    $switcher = [
        'name'     => 'MyField',
        'type'     => 'Switcher',
        'label'    => 'My Field',        
        'rules'    => [
            'custom' => function ($field) {
                $isValid = $field->isChecked();

                if (!$isValid)
                {
                    $field->setMessage('custom', 'Please enable this field');
                }

                return $isValid;
            },
        ],
    ];    
```

## Extends Field and Rule

Create all your fields at src/Field, the field must be extended \MaiVu\Php\Form\Field class

AND

Create all your rules at src/Rule, the rule must be extended \MaiVu\Php\Form\Rule class

OR 

if you want to use your custom namespace

```php
    
    use MaiVu\Php\Form\Form;
    
    Form::addFieldNamespaces('Your\\Custom\\MyNS');
    Form::addRuleNamespaces('Your\\Custom\\MyNS');        
```

Then create your FieldClass in your namespace

```php
   namespace Your\Custom\MyNS;
   use MaiVu\Php\Form\Field;
    
   class MyCustomField extends Field
   {
        public function toString()
        {
            return '<p>Hello World!</p>'; // Return input field
        }    
   }   
    
   // Usage: type => 'MyCustomField'
    
```

Create your RuleClass in your namespace

```php
   namespace Your\Custom\MyNS;
   use MaiVu\Php\Form\Rule;
   use MaiVu\Php\Form\Field;
    
   class MyCustomRule extends Rule
   {
        public function validate(Field $field) : bool 
        {
            return $field->getValue() === '1'; // Value = 1 is valid or not
        }    
   }   
    
   // Usage: rules => ['MyCustomRule']
    
```