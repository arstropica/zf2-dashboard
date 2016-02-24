// Report JS File
var gaugeOptions, updateChart, buildChart, rebuilding, chart;
buildChart = function(index, type) {
	$.getJSON('/report/ajax/data/' + index + '/' + type, function(data) {
		if (data.outcome) {
			var options = Highcharts.merge(gaugeOptions, data.data);
			$('#' + index + '_' + type).highcharts(options);
		}
	});
};
updateChart = function(data) {
	if (data && typeof data == 'object' && typeof chart != "undefined") {
		var info = data.message !== undefined ? data.message : false;
		if (info) {
			var status = info.status !== undefined ? info.status : false;
			if (status == 'running') {
				try {
					var layer = info.layer !== undefined ? info.layer : false;
					switch (layer) {
						case 'elastica':
							if (typeof info.count !== undefined) {
								chart.series[0].update({
									data : [ info.count ]
								});
							}
							break;
						case 'doctrine':
							if (typeof info.count !== undefined) {
								chart.yAxis.update({
									max : info.count
								});
							}
							break;
					}
				} catch (e) {}
			}
		}
	}
};

$(function() {
	// List Reports
	if ($('BODY.action-list.controller-index.module-report').length > 0) {
		gaugeOptions = JSON.parse($('#gaugeOptions').text());
		rebuilding = false;
		$('button.rebuildall').on('click', function(e) {
			var $btn = $(this);
			var $types = $btn.closest('.elastica-index').find('button.rebuild');
			var i = 0;
			if ($types.length > 0) {
				$btn.addClass('active');
				var indexTimer = setInterval(function() {
					if (i < $types.length && !rebuilding) {
						$types.eq(i).trigger('click');
						i++;
					} else if (i >= $types.length) {
						$btn.removeClass('active');
						clearInterval(indexTimer);
					}
				}, 500);
			}
		});
		$('button.rebuild').on('click', function(e) {
			rebuilding = true;
			var $btn = $(this);
			var hIndex = $btn.closest('.panel').find('.highchart').data('highchartsChart');
			var $cancel = $btn.closest('.panel').find('.cancel');
			chart = Highcharts.charts[hIndex];
			$btn.addClass('active');
			var cb = function(el) {
				var index = $(el).closest('.panel-footer').find('.index').eq(0).val();
				var type = $(el).closest('.panel-footer').find('.type').eq(0).val();
				if (index && type) {
					buildChart(index, type);
				}
				$btn.removeClass('active');
				$cancel.addClass('hidden');
				rebuilding = false;
			};
			var _updateChart = function(data) {
				updateChart(data);
			};
			$cancel.removeClass('hidden').on('click', function(e) {
				$(this).addClass('hidden');
				$.data($btn.get(0), 'eventsource').cancel(cb);
			});
			$(this).eventsource({
				log : false,
				onProgress : _updateChart
			}, cb);
		});
		$('.attributeType SELECT').on('change', function(e) {
			$.get('/report/ajax/attribute/' + $(this).data('id') + '?type=' + $(this).val());
		});
	}

	// Add Report
	if ($('BODY.action-add.controller-report.module-report, BODY.action-edit.controller-report.module-report, BODY.action-search.controller-report.module-lead').length > 0) {
		$('#criteria').delegate('.addremove-fieldset', 'formcollection_add', function() {
			init_controls($(this));
		});

		var init_location = function($field) {
			$field.find('.group-fieldset').gridwrap();
		};

		var init_controls = function($parent) {
			$parent = $parent || $(document);

			var selects = [ {
				selector : '.relationship',
				placeholder : 'Choose Comparision',
				max_selected_options : 1
			}, {
				selector : '.attribute',
				placeholder : 'Choose Attribute',
				max_selected_options : 1
			}, {
				selector : '.criterion.multiple',
				placeholder : 'Choose Value(s)',
				max_selected_options : Infinity
			}, {
				selector : '.criterion.state',
				placeholder : 'Choose State(s)',
				max_selected_options : Infinity
			} ];

			init_location($parent);
			$parent.find('.values-fieldset .form-group').hide();

			selects.forEach(function(select) {
				$parent.find(select.selector).initChosen({
					placeholder : select.placeholder,
					max : select.max_selected_options,
					force : true
				});
			});

			$parent.find('.attribute').fieldconditions({
				events : 'change load',
				target : $parent.find('.relationship'),
				ajaxUrl : function($obj) {
					return '/report/ajax/attribute/' + $obj.val() + '?action=relationship';
				},
				callback : function(target, data, response, e) {
					var success = false;
					var $container = $(this).closest('.addremove-fieldset');
					if (data.outcome !== undefined) {
						if (data.outcome == 1) {
							success = true;
							var rels = data.data;
							if (rels.length > 0) {
								var $relationship = $(target);
								var attribute_ids = $(this).val();
								var attribute_id = attribute_ids.length > 0 ? attribute_ids.pop() : false;
								$relationship.data('relationships', rels);
								$relationship.data('attribute', attribute_id);
								$relationship.children().hide();
								rels.forEach(function(rel) {
									$relationship.find('[value=' + rel.id + ']').show();
								});
								$relationship.enable().trigger("chosen:updated");
							}
						}
					}
					if (e.type == 'change') {
						if (!success) {
							$(target).disable().children().show();
						}
						if ($(target).is('[multiple]')) {
							$(target).val([]);
						} else {
							$(target).val('');
						}
					}
					$(target).trigger(e.type).trigger("chosen:updated");
				}
			});

			$parent.find('.relationship').fieldconditions({
				events : 'change load',
				target : $parent.find('#values'),
				data : {},
				callback : function(target, data, response, e) {
					var val = $(this).val();
					var rels = $(this).data('relationships');
					var attribute_id = $(this).data('attribute');
					var $container = $(this).closest('.addremove-fieldset').find('.values-fieldset');
					$container.find('.form-group').hide().disable();
					if (rels !== undefined && val !== undefined && val != '' && val != []) {
						var rel = rels.filter(function(r) {
							return val == r.id;
						});
						if (rel.length > 0) {
							var rel = rel.pop();
							var type = rel.type;
							var input = rel.input;
							if (input !== undefined) {
								$container.find('.type').val(type);
								var $field = $container.find('.form-group').has('.criterion.' + input);
								if (attribute_id) {
									var ajaxUrl = '/report/ajax/attribute/' + attribute_id + '?action=';
									switch (type) {
										case 'multiple':
											$.getJSON(ajaxUrl + 'values', function(response) {
												if (response.outcome !== undefined) {
													if (response.outcome == 1 && response.data !== undefined) {
														var values = response.data;
														var $control = $field.find('.criterion.' + input);
														$control.empty().append(function() {
															return values.map(function(value) {
																return $('<option>').val(value.value).text(value.value);
															});
														});
														if (e.type == 'load') {
															var values = $control.closest('#values').find('.value').val();
															if (values) {
																try {
																	values = JSON.parse(values);
																	$control.val(values);
																} catch (e) {}
															}
														}
														$field.fadeIn('slow', function() {
															$control.initChosen({
																placeholder : 'Choose Value(s)'
															});
															$(this).enable();
														});

													}
												}
											});
											break;
										case 'range':
											$.getJSON(ajaxUrl + 'limits', function(response) {
												if (response.outcome !== undefined) {
													if (response.outcome == 1 && response.data !== undefined) {
														var values = response.data;
														var ticks = [];
														var labels = [];
														if (values.max - values.min > 0) {
															var range = values.max - values.min;
															for (i = values.min; i <= values.max; i += (range / 4)) {
																ticks.push(i.toFixed(2));
																labels.push("" + i.toFixed(2));
															}
															var $control = $field.find('.criterion.' + input);
															var opts = {
																max : values.max,
																min : values.min,
																step : (range / 10).toFixed(2),
																ticks : ticks,
																ticks_labels : labels
															};
															$control.slider('destroy').slider(opts).slider('setValue', [ values.min, values.max ]);
														}
														$field.fadeIn('slow', function() {
															$(this).enable();
														});
													}
												}
											});
											break;
										case 'daterange':
											var $control = $field.find('.criterion.' + input);
											$field.fadeIn('slow', function() {
												$control.initDaterange();
												$(this).enable();
											});
											break;
										case 'location':
											var $control = $field.find('.criterion.' + input + '.state');
											init_location($field);
											$field.fadeIn('slow', function() {
												$control.initChosen({
													placeholder : 'Choose State(s)'
												});
												$(this).enable();
											});
											break;
									}
								}
								if ($field.is(':hidden')) {
									$field.fadeIn('slow', function() {
										$(this).enable();
									});
								}
							}
						}
					}
				}
			});

			$parent.find('._required').fieldconditions({
				events : 'change load',
				target : $parent.find('.weight'),
				data : {},
				callback : function(target, data, response, e) {
					var val = $(this).val();
					if (val == 1) {
						$(target).disable();
					} else {
						$(target).enable();
					}
				}
			});

			selects.forEach(function(select) {
				switch (select.selector) {
					case '.attribute':
						$parent.find(select.selector).each(function() {
							var val = $(this).val();
							if (val !== undefined && val != '' && val != []) {
								$(this).trigger('load');
							}
						});
						break;
				}
			});
			$parent.find('._required').trigger('load');
		};

		$('#account').initChosen({
			placeholder : 'Attach this Report to a Client Account',
			max : 1
		});
		$('#accountFilter').initChosen({
			placeholder : 'Filter Results by Account',
			max : 1
		});
		$('#orphan').on('change', function(e) {
			var val = $(this).val();
			if (val == 1) {
				$('#accountFilter').val([]).disable().trigger('chosen:updated');
			} else {
				$('#accountFilter').enable().trigger('chosen:updated');
			}
		}).trigger('change');

		$('#criteria .addremove-fieldset').each(function() {
			init_controls($(this));
		});

		$('#leadSearchForm').on('submit', function(e) {
			var required = false, fields;
			fields = $(this).serializeArray();
			if (fields && $.isArray(fields)) {
				fields.forEach(function(field) {
					if ((typeof field.name != 'undefined') && (/\[required\]$/i.test(field.name))) {
						required = (field.value == 1) ? true : required;
					}
				});
			}
			if (!required) {
				var r = confirm("Your search has no required criteria, and will return sorted, but unfiltered results.\n\nThis may take a long time to execute. Do you wish to proceed?");
				if (r == true) {
					return true;
				} else {
					e.preventDefault();
					return false;
				}
			}
		});
	}

});