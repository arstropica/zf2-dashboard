<?php
$form = $this->form;
$upload = $form->get('leadsUpload');
?>
<div class="col-md-12">
	<fieldset class="fieldset import">
		<legend><?php echo $upload->getLabel(); ?></legend>
		<div class="row import-fields">
			<div class="col-md-12">
					<?php echo $this->formRow($upload); ?>
					<small><i>Accepts:</i> <code>.csv, .xls, .xlsx</code> files.</small>
				<hr>
			</div>
			<div class="col-md-12"><?php echo $this->formRow($form->get('submit')); ?></div>
		</div>
	</fieldset>
</div>
