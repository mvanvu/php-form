# Php Form Package

Manage the form fields in easy way. Support templates: Bootstrap (3 and 4) and Uikit 3

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

```php
use MaiVu\Php\Form;

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
    echo 'Cool! insert data to the database';
}
else
{
    echo 'Oops. The form is invalid:<br/>' . implode('<br/>', $form->getMessages());
}

```

## Testing

1 - Clone this repo:

`   git clone https://github.com/mvanvu/php-form.git    
`

2 - Go to the repo

`
    cd php-from
`

3 - Run test server

`
php -S localhost:9000/tests
`

4 - Open the browser with url localhost:9000/tests

## Default fields see at path src/Field

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