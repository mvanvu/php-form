<?php
require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use MaiVu\Php\Form\Form;

$form = new Form(
	'',
	[
		[
			'name'  => 'hiddenField',
			'type'  => 'Hidden',
			'value' => uniqid(),
		],
		[
			'name'        => 'checkListField',
			'type'        => 'CheckList',
			'label'       => 'Check List Field',
			'required'    => true,
			'class'       => 'uk-checkbox',
			'description' => 'This is a check list field',
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
			'name'        => 'radioField',
			'type'        => 'Radio',
			'label'       => 'Radio Field',
			'required'    => true,
			'inline'      => true,
			'class'       => 'uk-radio',
			'description' => 'This is a radio field',
			'options'     => [
				[
					'value'    => 'Radio 1',
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
			'name'        => 'selectField',
			'type'        => 'Select',
			'label'       => 'Select Field',
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
			'name'        => 'textField',
			'type'        => 'Text',
			'label'       => 'Text Field',
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
			'name'     => 'pass1Field',
			'type'     => 'Password',
			'label'    => 'Password',
			'class'    => 'form-control',
			'required' => true,
		],
		[
			'name'         => 'pass2Field',
			'type'         => 'Password',
			'label'        => 'Confirm password',
			'class'        => 'form-control',
			'required'     => true,
			'rules'        => ['Confirm'],
			'confirmField' => 'pass1Field',
			'messages'     => [
				'Confirm' => 'Password is not match!',
			],
			'showOn'       => 'pass1Field : is not empty',
		],
		[
			'name'        => 'checkField',
			'type'        => 'Check',
			'label'       => 'Check Field',
			'checked'     => false,
			'value'       => 'Y',
			'filters'     => ['yesNo'],
			'description' => 'Check this field to see the textarea',
			'class'       => 'uk-checkbox',
		],
		[
			'name'        => 'textareaField',
			'type'        => 'TextArea',
			'label'       => 'TextArea Field',
			'class'       => 'form-control',
			'description' => 'This is a textarea field',
			'cols'        => 25,
			'rows'        => 5,
			'filters'     => ['basicHtml'],
			'required'    => true,
			'showOn'      => 'checkField : is checked',
		],
	]
);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Php Form Sample</title>
    <!--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"-->
    <!--          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">-->
    <!--    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.5.9/dist/css/uikit.min.css"/>-->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<div class="container uk-container uk-margin-auto mt-4 mb-4">
	<?php

	if (isset($_POST['hiddenField']))
	{
		$form->isValid($_POST);
	}

	?>
    <form method="post" novalidate>
		<?php echo $form->renderHorizontal(); ?>
        <div class="row">
            <div class="offset-sm-2 col-sm-10">
                <button class="btn btn-primary" type="submit">
                    Submit
                </button>
            </div>
        </div>
    </form>
</div>
<!--<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>-->
<script src="../assets/js/show-on.js?<?php echo time(); ?>"></script>
</body>
</html>