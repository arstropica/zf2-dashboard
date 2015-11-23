<?php
$form = $this->form;
$confirm = $form->get('confirm');
$data = $this->data;
$invalid = $this->valid;
$headings = $this->headings;
?>
<div class="col-md-12">
	<fieldset class="fieldset confirm">
		<legend><?php echo $confirm->getLabel(); ?></legend>
		<div class="row import-fields">
			<div class="col-md-12">
				<div class="panel-group" id="accordion">
			      <?php
                    $i = 0;
                    foreach ($data as $index => $record) :
                        $labels = [];
                        foreach ([
                            'First Name',
                            'Last Name'
                        ] as $desc) {
                            $key = isset($headings[$desc]) ? $headings[$desc] : false;
                            if ($key && isset($record[$key])) {
                                $labels[$desc] = current($record[$key]);
                            } else {
                                $labels[$desc] = "";
                            }
                        }
                        if (isset($invalid[$index]) && $invalid[$index]) {
                            continue;
                        }
                  ?>
                    <div class="panel panel-default"
						id="<?php echo "panel{$index}"; ?>">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a data-toggle="collapse"
									data-target="<?php echo "#data{$index}"; ?>"
									href="<?php echo "#data{$index}"; ?>"> <span class="record"> <span
										class="name pull-left">
                                        <?php echo ucwords($labels['First Name'] . " " . $labels['Last Name']); ?>
                                      </span>
										<br class="clearfix" style="height: 0px; clear: both;" />
								</span>
								</a>
							</h4>
						</div>
						<div id="<?php echo "data{$index}"; ?>"
							class="panel-collapse collapse <?php echo $i ? "" : "in"; ?>">
							<div class="panel-body">
								<table class="table table-condensed table-striped"
									class="margin: 0px;">
									<thead>
										<tr>
											<th>Field</th>
											<th>Imported Record</th>
										</tr>
									</thead>
									<tbody>
			                          <?php foreach ($record as $attribute_id => $entry) : ?> 
			                              <?php foreach ($entry as $data_label => $data_value) : ?> 
				                            <tr>
    											<td><?php echo $data_label; ?></td>
    											<td><?php echo $data_value; ?></td>
										    </tr>
			                             <?php endforeach; ?> 
			                          <?php endforeach; ?> 
			                          </tbody>
								</table>
							</div>
						</div>
					</div>
                    <?php $i ++; endforeach; ?>
                </div>
            </div>
			<div class="col-md-12">
				<div class="form-group">
				    <?php echo $this->formElement($form->get('submit')) . "&nbsp;&nbsp;&nbsp;" . $this->formElement($form->get('cancel')); ?>
			    </div>
			</div>
		</div>
	</fieldset>
</div>
<?php
foreach ($form as $name => $element) {
    switch ($name) {
        case 'submit':
            break;
        default:
            echo $this->formHidden($element);
            break;
    }
}
