<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use MaiVu\Php\Assets;
use MaiVu\Php\Form\Field\Check;
use MaiVu\Php\Form\Form;

$form = new Form(
	[
		[
			'name'  => 'hiddenField',
			'type'  => 'Hidden',
			'value' => uniqid(),
		],
		[
			'name'        => 'switcher',
			'type'        => 'Switcher',
			'label'       => 'Switcher',
			'value'       => 'YES',
			'filters'     => ['YES|NO'],
			'description' => 'Toggle this to show/hide the Check List',
			'checked'     => true,
		],
		[
			'name'        => 'checkList',
			'type'        => 'CheckList',
			'label'       => 'Check List',
			'required'    => true,
			'class'       => 'uk-checkbox',
			'description' => 'This is a check list field',
			'showOn'      => 'switcher:[-]',
			'options'     => [
				[
					'value'    => 'Check 1',
					'text'     => 'Check 1',
					'readonly' => true,
				],
				[
					'value'    => 'Check 2',
					'text'     => 'Check 2',
					'disabled' => true,
				],
				[
					'value' => 'Check 3',
					'text'  => 'Check 3',
					'class' => 'extra-class',
				],
			],
		],
		[
			'name'        => 'radio',
			'type'        => 'Radio',
			'label'       => 'Radio',
			'required'    => true,
			'inline'      => true,
			'class'       => 'uk-radio',
			'description' => 'This is a radio field',
			'options'     => [
				[
					'value'    => 'Check 1',
					'text'     => 'Radio 1',
					'readonly' => true,
				],
				[
					'value'    => 'Radio 2',
					'text'     => 'Radio 2',
					'disabled' => true,
				],
				[
					'value' => 'Radio 3',
					'text'  => 'Radio 3',
					'class' => 'extra-class',
				],
			],
		],
		[
			'name'        => 'select',
			'type'        => 'Select',
			'label'       => 'Select',
			'class'       => 'form-control',
			'value'       => 'optValue1',
			'options'     => [
				[
					'value'    => 'Option 1',
					'text'     => 'Option 1',
					'readonly' => true,
				],
				[
					'value'    => 'Option 2',
					'text'     => 'Option 2',
					'disabled' => true,
				],
				[
					'label'    => 'Group Label',
					'class'    => 'extra-class',
					'optgroup' => [
						[
							'value'    => 'Group Label 1',
							'text'     => 'Group Label 1',
							'readonly' => true,
						],
						[
							'value'    => 'Group Label 2',
							'text'     => 'Group Label 2',
							'disabled' => true,
						]
					],
				],
			],
			'rules'       => ['Options'],
			'description' => 'This is a select field',
		],
		[
			'name'        => 'email',
			'type'        => 'Email',
			'label'       => 'Email',
			'class'       => 'form-control',
			'hint'        => 'Please enter a valid email',
			'description' => 'This is a email field',
			'required'    => true,
		],
		[
			'name'        => 'text',
			'type'        => 'Text',
			'label'       => 'Text',
			'class'       => 'form-control',
			'value'       => 'Default text value',
			'hint'        => 'Text placeholder',
			'required'    => true,
			'messages'    => [
				'requireMessage' => 'The textField is required.',
			],
			'description' => 'This is a text field',
		],
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
		],
		[
			'name'        => 'check',
			'type'        => 'Check',
			'label'       => 'Check',
			'checked'     => false,
			'value'       => 'Y',
			'filters'     => ['yesNo'],
			'description' => 'Check this field and enter the password to see the textarea',
			'class'       => 'uk-checkbox',
			'rules'       => [
				'-:textarea|!',
				'custom' => function (Check $field) {

					if (!($isChecked = $field->isChecked()))
					{
						$field->setMessage('custom', 'You must check on this field.');
					}

					return $isChecked;
				},
			],
			'messages'    => [
				'-:textarea|!' => 'The textarea must not be empty.',
				'custom'       => 'You must check on this field.'
			],
		],
		[
			'name'        => 'textarea',
			'type'        => 'TextArea',
			'label'       => 'TextArea',
			'class'       => 'form-control',
			'description' => 'This is a textarea field',
			'cols'        => 25,
			'rows'        => 5,
			'filters'     => ['basicHtml'],
			'required'    => true,
			'showOn'      => 'check:[-] & pass1:!',
			'rules'       => ['>=:5', '<=:15', '#:^[0-9a-zA-Z]+$'],
			'messages'    => [
				'>=:5'             => 'Min length is 5',
				'<=:15'            => 'Max length is 15',
				'#:^[0-9a-zA-Z]+$' => 'Must be alpha num!',
			],
		],
	]
);

if (isset($_POST['hiddenField']))
{
	$form->isValid($_POST);
}

Assets::setDebugMode(true);
Assets::addFile('https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');

// UIkit 3
// Form::setTemplate('uikit-3');
// Assets::addFile('https://cdn.jsdelivr.net/npm/uikit@3.5.9/dist/css/uikit.min.css');

// Optional Using JQuery to get a smooth show-on
Assets::addFile('https://code.jquery.com/jquery-3.5.1.min.js');

$renderForm = $form->renderHorizontal();
Assets::compress();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Php Form Sample</title>
	<?php echo Assets::output('css'); ?>
</head>
<body>
<div class="container uk-container uk-margin-auto mt-4 mb-4">
    <form method="post" novalidate>
		<?php echo $renderForm; ?>
        <div class="row">
            <div class="offset-sm-2 col-sm-10">
                <button class="btn btn-primary" type="submit">
                    Submit
                </button>
            </div>
        </div>
    </form>
</div>

<?php echo Assets::output('js'); ?>
</body>
</html>