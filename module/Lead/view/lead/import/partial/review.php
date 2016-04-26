<?php
use Application\Utility\Helper;

$form = $this->form;
$review = $form->get('review');
$duplicates = ($form->has('duplicate')) ? $form->get('duplicate') : false;
$headings = $this->headings;
$invalid = array_filter($this->valid);
?>
<div class="col-md-12">
	<fieldset class="fieldset review">
		<legend><?php echo $review->getLabel(); ?></legend>
		<div class="row import-fields">
			<div class="col-md-12">
				<div class="panel-group" id="accordion">
				      <?php
        $i = 0;
        foreach ($invalid as $index => $record) :
            $data = isset($record['record']) ? $record['record'] : false;
            $fields = $record['fields'];
            $duplicate = $record['duplicate'];
            $labels = [
                'Score' => $record['score']
            ];
            foreach ([
                'First Name',
                'Last Name'
            ] as $desc) {
                $key = isset($headings[$desc]) ? $headings[$desc] : false;
                if ($key && isset($data[$key])) {
                    $labels[$desc] = current($data[$key]);
                } else {
                    $labels[$desc] = "";
                }
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
                                      </span> <span class="score"><i>Similarity:</i><span><?php echo $labels['Score'] . "%"; ?></span></span>
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
											<th>Existing Record</th>
										</tr>
									</thead>
									<tbody>
			                          <?php foreach ($data as $attribute_id => $entry) : ?> 
			                              <?php foreach ($entry as $data_label => $data_value) : ?>
			                              <?php if (is_array($data_value)) $data_value = Helper::recursive_implode($data_value); ?> 
				                            <tr
											class="<?php if (isset($fields[$attribute_id])) echo "duplicate"; ?>">
											<td><?php echo $data_label; ?></td>
											<td><?php echo $data_value; ?></td>
											<td>
				                                      <?php
                    if (isset($fields[$attribute_id])) :
                        echo $fields[$attribute_id];
                     elseif (isset($duplicate[$attribute_id])) :
                        if ($duplicate[$attribute_id] instanceof \DateTime)
                            echo date_format($duplicate[$attribute_id], 'Y-m-d H:i:s');
                        elseif (is_array($duplicate[$attribute_id]))
                            echo current($duplicate[$attribute_id]);
                        else
                            echo $duplicate[$attribute_id];
                    endif;
                    ?>
					                            </td>
										</tr>
			                             <?php endforeach; ?> 
			                          <?php endforeach; ?> 
			                          </tbody>
								</table>
							</div>
							<div class="panel-footer">
								<div class="validate-record">
									<span class="pull-left"> <label>Import Record ?</label>
									</span> <span class="include-record pull-right">
							            <?php if ($duplicates) echo $this->formElement($duplicates->get($index)); ?>
							        </span>
								</div>
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
        case 'duplicate':
            break;
        default:
            echo $this->formHidden($element);
            break;
    }
}
?>
<script type="text/javascript">
	if (typeof $.fn.bootstrapSwitch !== 'undefined') {
    	$('.review-switch').bootstrapSwitch();
	}
	$('#accordion').on('show.bs.collapse', function () {
	    $('#accordion .panel-collapse.in').collapse('hide');
	});
</script>
