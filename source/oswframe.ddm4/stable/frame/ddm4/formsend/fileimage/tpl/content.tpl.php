<?php

/**
 * This file is part of the osWFrame package
 *
 * @author Juergen Schwind
 * @copyright Copyright (c) JBS New Media GmbH - Juergen Schwind (https://jbs-newmedia.com)
 * @package osWFrame
 * @link https://oswframe.com
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License 3
 */

?>

<div class="form-group ddm_element_<?php echo $this->getSendElementValue($element, 'id') ?>">

	<?php /* label */ ?>
	<label for="<?php echo $element ?>"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementValue($element, 'title')) ?><?php if ($this->getSendElementOption($element, 'required')===true): ?><?php echo $this->getGroupMessage('form_title_required_icon') ?><?php endif ?><?php echo $this->getGroupMessage('form_title_closer') ?></label>

	<?php if ($this->getSendElementOption($element, 'read_only')===true): ?>

		<?php /* read only */ ?>
		<div class="form-control readonly">
			<?php if ($this->getSendElementStorage($element)!=''): ?>
				<a target="_blank" href="<?php echo $this->getSendElementStorage($element) ?>"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_view')) ?></a>
				<?php $this->getTemplate()->Form()->drawHiddenField($element, $this->getSendElementStorage($element)) ?><?php else: ?><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_blank')) ?><?php endif ?>
		</div>

	<?php else: ?>

	<?php /* input */ ?>
		<div class="custom-file">
			<?php echo $this->getTemplate()->Form()->drawFileField($element, $this->getSendElementStorage($element), ['input_class'=>'custom-file-input', 'input_errorclass'=>'is-invalid']) ?>
			<label class="custom-file-label" for="<?php echo $element ?>"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_select')) ?></label>
		</div>
		<script>
			$("#<?php echo $element?>").on("change", function () {
				var fileName = $(this).val().split("\\").pop();
				$(this).siblings(".custom-file-label").addClass("selected").html(fileName);
			});
		</script>

	<?php endif ?>

	<?php /* error */ ?>
	<?php if ($this->getTemplate()->Form()->getErrorMessage($element)): ?>
		<div class="text-danger small"><?php echo $this->getTemplate()->Form()->getErrorMessage($element) ?></div>
	<?php endif ?>

	<?php /* notice */ ?>
	<?php if ($this->getSendElementOption($element, 'notice')!=''): ?>
		<div class="text-info"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'notice')) ?></div>
	<?php endif ?>

	<?php /* misc */ ?>
	<?php if (($this->getDoSendElementStorage($element.$this->getSendElementOption($element, 'temp_suffix'))!='')&&($this->getSendElementOption($element, 'read_only')!==true)): ?>
		<div class="custom-control custom-checkbox">
			<?php echo $this->getTemplate()->Form()->drawCheckBoxField($element.$this->getSendElementOption($element, 'temp_suffix').$this->getSendElementOption($element, 'delete_suffix'), 1, 0, ['input_parameter'=>'title="'.\osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_delete')).'"', 'input_class'=>'custom-control-input']) ?>
			<label class="custom-control-label" for="<?php echo $element.$this->getSendElementOption($element, 'temp_suffix').$this->getSendElementOption($element, 'delete_suffix') ?>0"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_delete')) ?></label>
		</div>
		<?php $this->getTemplate()->Form()->drawHiddenField($element.$this->getSendElementOption($element, 'temp_suffix'), $this->getDoSendElementStorage($element.$this->getSendElementOption($element, 'temp_suffix'))) ?><?php $this->getTemplate()->Form()->drawHiddenField($element, $this->getDoSendElementStorage($element)) ?><?php elseif (($this->getSendElementStorage($element)!='')&&($this->getSendElementOption($element, 'read_only')!==true)): ?>
		<div class="custom-control custom-checkbox">
			<?php echo $this->getTemplate()->Form()->drawCheckBoxField($element.$this->getSendElementOption($element, 'delete_suffix'), 1, 0, ['input_parameter'=>'title="'.\osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_delete')).'"', 'input_class'=>'custom-control-input']) ?>
			<label class="custom-control-label" for="<?php echo $element.$this->getSendElementOption($element, 'delete_suffix') ?>0"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_delete')) ?></label>
		</div>
		<?php $this->getTemplate()->Form()->drawHiddenField($element, $this->getSendElementStorage($element)) ?><?php endif ?>

	<?php /* buttons */ ?>
	<?php if (($this->getSendElementOption($element, 'buttons')!='')||(($this->getDoSendElementStorage($element.$this->getSendElementOption($element, 'temp_suffix'))!='')||($this->getSendElementStorage($element)!='')&&($this->getSendElementOption($element, 'read_only')!==true))): ?>
		<div>
			<?php if ($this->getDoSendElementStorage($element.$this->getSendElementOption($element, 'temp_suffix'))!=''): ?>
				<a target="_blank" class="btn btn-secondary btn-sm" href="<?php echo $this->getDoSendElementStorage($element.$this->getSendElementOption($element, 'temp_suffix')) ?>"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_view')) ?></a>
			<?php elseif ($this->getSendElementStorage($element)!=''): ?>
				<a target="_blank" class="btn btn-secondary btn-sm" href="<?php echo $this->getSendElementStorage($element) ?>"><?php echo \osWFrame\Core\HTML::outputString($this->getSendElementOption($element, 'text_file_view')) ?></a>
				<?php $this->getTemplate()->Form()->drawHiddenField($element, $this->getSendElementStorage($element)) ?><?php endif ?>
			<?php echo implode(' ', $this->getSendElementOption($element, 'buttons')) ?>
		</div>
	<?php endif ?>

</div>