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

## Show on feature
Show or hide the base field in the conditions (like Joomla! CMS Form)

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

* {fieldName} : is checked // Show when the {fieldName} is checked
* {fieldName} : is not checked // Show when the {fieldName} is not checked
* {fieldName} : is selected // Show when the {fieldName} is selected
* {fieldName} : is not selected // Show when the {fieldName} is not checked
* {fieldName} : is empty // Show when the {fieldName} is empty
* {fieldName} : is not empty // Show when the {fieldName} is not empty
* {fieldName} : abc123 // Show when the {fieldName} has value == abc123
* {fieldName} : !abc123 // Show when the {fieldName} has value != abc123

### AND Operator (&)
* {fieldName} : is not empty & {fieldName} : abc123

### OR Operator (|)
* {fieldName} : is not empty | {fieldName} : abc123

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

4 - Open the browser with url localhost:9000/tests

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