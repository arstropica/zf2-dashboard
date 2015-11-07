/**
 * Application JS
 */
$.fn.wysiwygEvt = function() {
	return this.each(function() {
		var $this = $(this);
		var htmlold = $this.html();
		$this.bind('blur paste', function() {
			var htmlnew = $this.html();
			if (htmlold !== htmlnew) {
				htmlold = $this.html();
				$this.trigger('change')
			}
		})
	})
};

$.fn.sortTable = function(options) {
	var opts = options || {};
	var _start;
	if (opts.hasOwnProperty("_start")) {
		_start = opts._start;
		delete opts._start;
	} else {
		_start = 0;
	}
	if (opts.hasOwnProperty("_order")) {
		_order = opts._order;
		delete opts._order;
	} else {
		_order = 'asc';
	}
	var between = function(x, min, max, eq) {
		eq = eq || '>';
		if (eq == '>') {
			return x >= min && x < max;
		} else {
			return x > min && x <= max;
		}
	}
	var isInt = function(value) {
		if (isNaN(value)) {
			return false;
		}
		var x = parseFloat(value);
		return (x | 0) === x;
	}
	var onDrag = function(event, ui) {
		if (ui !== undefined) {
			ui.item.startPos = ui.item.index();
		}
	};
	var onUpdate = function(event, ui) {
		if (ui !== undefined) {
			var $row = ui.item;
			var new_index = _start + ui.item.index();
			var old_index = _start + ui.item.startPos;
			ajaxUpdate($row, new_index, old_index, 1);
		}
	};
	var ajaxUpdate = function($row, new_index, old_index, domUpdate) {
		var id = $row.find('.identifier').val();
		var success = false;
		var $node = $row.parent();
		domUpdate = domUpdate || 0;
		$.post('/attribute/sort/' + id, {
			index : new_index,
			order : _order,
			domUpdate : domUpdate
		}, function(data) {
			if (typeof data == 'object') {
				if (typeof data.result != 'undefined') {
					if (data.result) {
						success = true;
					}
				}
			}
			if (!success) {
				$node.sortable("cancel");
			} else if (domUpdate) {
				if (data.hasOwnProperty('collection')) {
					var collection = data.collection;
					var $_sortable = $row.parent();
					collection.forEach(function(a, i){
						var $tr = $_sortable.find('TR#'+a.id);
						if ($tr.length) {
							$tr.find('.sortable-order').html(a.attributeOrder);
							$_sortable.append($tr);
						}
					});
					$node.sortable('refresh');
				}
			}
		}, 'json');
	};
	return $(this).each(function() {
		var handle;
		var $_sortable = $(this);
		if ($.fn.sortable) {
			if (opts.hasOwnProperty('handle')) {
				handle = opts.handle;
				$(handle).append('<div class="handlebars"><span class="grip"></span></div>');
				opts.handle = '.handlebars';
			}
			$_sortable.closest('TABLE').addClass('sortTable');
			$_sortable.find('.sortable-order').each(function() {
				var old_position = isInt($(this).text()) ? $(this).text() : _start + $(this).index();
				$(this).data('old_position', old_position);
				// $(this).prop('contentEditable', true);
			});
			$_sortable.find('.sortable-order').each(function() {
				var $sortable_order = $(this);
				$sortable_order.wysiwygEvt().on('change', function(e) {
					var order = isInt($sortable_order.text()) ? parseInt($sortable_order.text()) : false;
					if (order !== false) {
						var $row = $sortable_order.closest('TR');
						var old_index = $row.index();
						var new_index = order - _start;
						var old_position = $sortable_order.data('old_position');
						var new_position = order;
						/*var $target = $_sortable.children().eq(new_position);
						if ($target.length) {
							$row.insertBefore($target);
						} else {
							$row.insertAfter($_sortable.children().last());
						}*/
						$sortable_order.data('old_position', order);
						ajaxUpdate($row, new_position, old_position, 0);
						$_sortable.sortable('refresh');
						$_sortable.sortable('refreshPositions');
					}
				});
			});
			$_sortable.sortable(opts);
			$_sortable.on('sortstart', onDrag);
			$_sortable.on('sortupdate', onUpdate);
		}
	});
}

$(function() {
	var $enabled = $("TABLE.sel TD .checkbox INPUT[type=checkbox][disabled!=disabled]");
	if ($enabled.length == 0) {
		$('#batchsubmitbutton').prop('disabled', 'disabled');
	}
	$('TABLE.sel TH .selall').click(function() {
		$enabled.prop("checked", $(this).prop("checked"));
	});
	$('#exportLeads').on('click', function(e) {
		e.preventDefault();
		var url = $(this).prop('href');
		var params = $('#leadFilterForm').serialize();
		window.location.href = url + '?' + params;
		return false;
	});

	$('#leadimport .import-fields SELECT').on('change', function(e) {
		$(this).closest('.form-group').find('.desc').remove();
		var val = $(this).val();
		var match = $(this).data('match');

		if (val === "Question") {
			$(this).removeClass('custom match ignore').addClass("new").prop('title', 'You want this to be a new field.').after('<span class="desc new">New Field</span>');
		} else if (val !== "" && val != match) {
			$(this).removeClass('match new ignore').addClass('custom').prop('title', 'You have custom mapped this field.').after('<span class="desc custom">Custom Field</span>');
		} else if (val !== "" && val == match) {
			$(this).removeClass('custom new ignore').addClass('match').prop('title', 'This field has been automatically mapped.').after('<span class="desc match">Matched Field</span>');
		} else if (val === "ignore") {
			$(this).removeClass('custom match new').addClass("ignore").prop('title', 'You have ignored this field.').after('<span class="desc ignore">Ignored Field</span>');
		} else if (val === "") {
			$(this).removeClass('custom match ignore new');
		}
	}).trigger('change');
	$('.table-collapse > TBODY > TR.collapsed').hover(function() {
		$(this).find('TD').attr('title', '[+] Expand');
	}, function() {
		$(this).find('TD').removeAttr('title');
	});
	$('.table-collapse > TBODY > TR:not(.collapsed)').hover(function() {
		$(this).find('TD').attr('title', '[-] Collapse');
	}, function() {
		$(this).find('TD').removeAttr('title');
	});
	autosize($('textarea.autoresize'));
});