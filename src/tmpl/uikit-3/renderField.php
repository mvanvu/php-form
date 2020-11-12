<?php

use MaiVu\Php\Form\Field;

/**
 * @var Field $this
 * @var array $displayData
 */


$fieldClass = ($displayData['horizontal'] ? 'uk-form-horizontal ' : '') . 'uk-margin ' . $displayData['class'];

?>
<div class="<?php echo $fieldClass; ?>"<?php echo $displayData['showOn'] ? ' data-show-on="' . htmlspecialchars(json_encode($displayData['showOn']), ENT_COMPAT, 'UTF-8') . '"' : ''; ?>>
	<?php if (!empty($displayData['label'])): ?>
        <label class="uk-form-label" for="<?php echo $displayData['id']; ?>">
			<?php echo $this->_($displayData['label']) . ($displayData['required'] ? '*' : ''); ?>
        </label>
	<?php endif; ?>
    <div class="uk-form-controls">
		<?php echo $this->input; ?>

		<?php if ($displayData['errors']): ?>
            <div id="<?php echo $displayData['id'] . '-errors-msg'; ?>">
                <small class="uk-form-controls-text uk-text-danger">
					<?php echo implode('<br/>', $displayData['errors']); ?>
                </small>
            </div>
		<?php endif; ?>

		<?php if ($displayData['description']): ?>
            <div id="<?php echo $displayData['id'] . '-desc'; ?>">
                <small class="uk-form-controls-text uk-text-muted">
					<?php echo $this->_($displayData['description']); ?>
                </small>
            </div>
		<?php endif; ?>
    </div>
</div>