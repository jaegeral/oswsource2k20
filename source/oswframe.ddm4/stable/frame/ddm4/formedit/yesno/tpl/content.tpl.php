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

<div class="form-group ddm_element_<?php echo $this->getEditElementValue($element, 'id') ?>">

	<?php /* label */ ?>
	<label><?php echo \osWFrame\Core\HTML::outputString($this->getEditElementValue($element, 'title')) ?><?php if ($this->getEditElementOption($element, 'required')===true): ?><?php echo $this->getGroupMessage('form_title_required_icon') ?><?php endif ?><?php echo $this->getGroupMessage('form_title_closer') ?></label><br/>

	<?php if ($this->getEditElementOption($element, 'read_only')===true): ?>

		<?php /* read only */ ?><?php if ($this->getEditElementStorage($element)=='1'): ?>
			<div class="form-control readonly"><?php echo \osWFrame\Core\HTML::outputString($values['options']['text_yes']) ?></div>
		<?php elseif ($this->getEditElementStorage($element)=='0'): ?>
			<div class="form-control readonly"><?php echo \osWFrame\Core\HTML::outputString($values['options']['text_no']) ?></div>
		<?php else: ?>
			<div class="form-control readonly"><?php echo \osWFrame\Core\HTML::outputString($values['options']['text_blank']) ?></div>
		<?php endif ?><?php echo $this->getTemplate()->Form()->drawHiddenField($element, $this->getEditElementStorage($element)) ?>

	<?php else: ?>

		<?php /* input */ ?><?php foreach (str_split($this->getEditElementOption($element, 'displayorder')) as $key): ?><?php if ($key=='y'): ?>
			<div class="form-check-inline">
				<label class="form-check-label">
					<div class="custom-control custom-radio">
						<?php echo $this->getTemplate()->Form()->drawRadioField($element, '1', $this->getEditElementStorage($element), ['input_class'=>'custom-control-input']) ?>
						<label class="custom-control-label<?php if ($this->getTemplate()->Form()->getErrorMessage($element)): ?> text-danger<?php endif ?>" for="<?php echo $element ?>0"><?php echo \osWFrame\Core\HTML::outputString($values['options']['text_yes']) ?></label>
					</div>
				</label>
			</div>
		<?php endif ?><?php if ($key=='n'): ?>
			<div class="form-check-inline">
				<label class="form-check-label">
					<div class="custom-control custom-radio">
						<?php echo $this->getTemplate()->Form()->drawRadioField($element, '0', $this->getEditElementStorage($element), ['input_class'=>'custom-control-input']) ?>
						<label class="custom-control-label<?php if ($this->getTemplate()->Form()->getErrorMessage($element)): ?> text-danger<?php endif ?>" for="<?php echo $element ?>1"><?php echo \osWFrame\Core\HTML::outputString($values['options']['text_no']) ?></label>
					</div>
				</label>
			</div>
		<?php endif ?><?php endforeach ?>

	<?php endif ?>

	<?php /* error */ ?>
	<?php if ($this->getTemplate()->Form()->getErrorMessage($element)): ?>
		<div class="text-danger small"><?php echo $this->getTemplate()->Form()->getErrorMessage($element) ?></div>
	<?php endif ?>

	<?php /* notice */ ?>
	<?php if ($this->getEditElementOption($element, 'notice')!=''): ?>
		<div class="text-info"><?php echo \osWFrame\Core\HTML::outputString($this->getEditElementOption($element, 'notice')) ?></div>
	<?php endif ?>

	<?php /* buttons */ ?>
	<?php if ($this->getEditElementOption($element, 'buttons')!=''): ?>
		<div>
			<?php echo implode(' ', $this->getEditElementOption($element, 'buttons')) ?>
		</div>
	<?php endif ?>

</div>