<?php
$form = $this->form;
$match = $form->get('match');
?>
<div class="col-md-12">
	<?php echo $this->formRow($form->get('Company')); ?>

	<fieldset class="fieldset import-fields match">
		<legend><?php echo $match->getLabel(); ?></legend>
		<?php if ($match instanceof Zend\Form\Fieldset) : ?>
			<?php  foreach ($match as $fieldName => $fieldSet) : ?>
				<?php foreach ($fieldSet as $element) : ?>
					<?php
            $match = array_search(strtolower($fieldName), array_map('strtolower', $this->fields));
            if (($match !== false) && empty($element->getValue())) {
                $element->setValue($match)
                    ->setAttribute("class", $element->getAttribute("class") . " match")
                    ->setAttribute('data-match', $match)
                    ->setAttribute('required', 'required');
            }
            ?>
					<div class="col-xs-12 col-sm-6 col-md-4 importField">
						 <?php echo $this->formRow($element); ?>
					</div>
				<?php endforeach; ?>
			<?php endforeach; ?>
		<?php endif; ?>
		<div class="row import-fields">
			<div class="col-md-12">
				<div class="well">
					<code>Leads Found: <?php echo $this->count; ?></code>
				</div>
			</div>
		</div>
	</fieldset>
	<hr>
	<div class="row">
		<div class="col-md-12"><?php echo $this->formRow($form->get('submit')); ?></div>
	</div>
</div>
<?php echo $this->formelement($form->get('leadTmpFile')); ?>
<?php echo $this->losHeadLink()->setBasePath('/js')->appendChosen()?>
<?php echo $this->losHeadScript()->setBasePath('/js')->appendChosen()?>
<script type="text/javascript">
	$('.importSelect').prop('multiple', 'multiple').filter(function() {
        return $.trim( $(this).val() ) == '';
    }).prop("selected", false).val([]);
	<?= $this->losChosen('.importSelect',['disable_search_threshold'=>2, 'max_selected_options'=>1, 'allow_single_deselect'=>true])?>
</script>
