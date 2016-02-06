/**
 * Application JS
 */

Object.filter = function(obj, predicate, reverse) {
	reverse = reverse || false;
	var result = {}, key;
	// ---------------^---- as noted by @CMS,
	// always declare variables with the "var" keyword

	for (key in obj) {
		if (obj.hasOwnProperty(key)) {
			if (reverse && predicate(obj[key])) {
				result[key] = obj[key];
			} else if (!reverse && !predicate(obj[key])) {
				result[key] = obj[key];
			}
		}
	}

	return result;
};

Object.depthOf = function(obj) {
	var level = 1;
	var key;
	for (key in obj) {
		if (!obj.hasOwnProperty(key)) {
			continue;
		}

		if (typeof obj[key] == 'object') {
			var depth = Object.depthOf(obj[key]) + 1;
			level = Math.max(depth, level);
		}
	}
	return level;
};

String.prototype.toProperCase = function() {
	return this.replace(/\w\S*/g, function(txt) {
		return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
	});
};

$.fn.disable = function() {
	return $(this).each(function() {
		$(this).attr('disabled', 'disabled');
		$(this).addClass('disabled');
	});
};
$.fn.enable = function() {
	return $(this).each(function() {
		$(this).removeClass('disabled');
		$(this).removeAttr('disabled');
	});
};

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
			return (x >= min) && (x < max);
		} else {
			return (x > min) && (x <= max);
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
					collection.forEach(function(a, i) {
						var $tr = $_sortable.find('TR#' + a.id);
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
				// $(this).prop('contentEditable',
				// true);
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
						/*
						 * var $target = $_sortable.children().eq(new_position);
						 * if ($target.length) { $row.insertBefore($target); }
						 * else {
						 * $row.insertAfter($_sortable.children().last()); }
						 */
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
};

$.fn.json2html = function(data, options) {
	if (!$.isPlainObject(data)) {
		return this;
	}

	var defaults = {
		max_tab_level : -1,
		toggle : 'pill'
	};
	var opts = $.extend(true, {}, defaults, options);

	var render = function(_data, _heading, _level, _siblings, $parent) {
		$parent = $parent || false;
		_level = _level || 0;
		_siblings = _siblings || 1;
		_heading = _heading || 'overview';
		var parent_id = $parent.prop('rel');
		var heading = parent_id ? parent_id + '_' + _heading : _heading;
		var _depth = Object.depthOf(_data) - 1;
		var max_tab_level = opts.max_tab_level > 0 ? opts.max_tab_level : _depth + _level + opts.max_tab_level;
		var $container = $('<div>').addClass(
				'level lvl-' + _level + ' col-xs-12 col-md-' + (((_level > 1) && (_level < max_tab_level)) ? Math.floor(12 / _siblings) : 12));
		var $output = $parent ? $parent : $('<div>').addClass('json2html root_node row');
		var $h;
		var nested = false;
		var len = Object.keys(_data).length;
		var $subsection = $('<div>').addClass('row padding-v-med').prop('rel', _heading);

		if (!$.isEmptyObject(Object.filter(_data, $.isPlainObject, true))) {
			if (_level < max_tab_level) {
				var $tabs = toTabs(_data, _heading);
				$container.append($tabs);
				$subsection.addClass('tab-content');
			}
			if ((_level > 0) && (_level <= max_tab_level)) {
				$container.prop('id', heading).attr('role', 'tabpanel').addClass('tab-pane');
				if (_level == max_tab_level) {
					$subsection.addClass('row-flex row-flex-wrap');
				}
			}
		}

		for (_key in _data) {
			if ($.isPlainObject(_data[_key])) {
				if ($.isEmptyObject(Object.filter(_data[_key], $.isPlainObject, true))) {
					if (_level == 0) {
						var __data = {};
						__data[_key] = _data[_key];
						render(__data, _key, _level + 1, len, $subsection);
						// $subsection.append(toPanel(_key, _data[_key], len));
					} else {
						$subsection.append(toPanel(_key, _data[_key], len));
					}
				} else {
					render(_data[_key], _key, _level + 1, len, $subsection);
				}
			}
		}
		$container.append($subsection);
		$output.append($container);
		return $output;
	};

	var toTabs = function(_data, _heading) {
		var $tabs = $('<ul>').addClass('json-tab nav nav-' + opts.toggle + 's').prop('id', 'tab-' + _heading);
		if (typeof _data == 'object') {
			for (_key in _data) {
				var $li = $('<li role="tab">');
				$li.append($('<a data-toggle="' + opts.toggle + '">').text(deslugify(_key)).prop('href', '#' + _heading + '_' + _key));
				$tabs.append($li);
			}
		}
		return $tabs;
	};

	var toPanel = function(_heading, _data, _siblings) {
		var _siblings = _siblings || 1;
		var min_w = 4;
		var $container = $('<div>').addClass('flex-col col-xs-12 col-md-' + Math.max(min_w, Math.floor(12 / _siblings)));
		var $panel = $('<div>').addClass('panel panel-default');
		var $heading = $('<div>').addClass('panel-heading').text(deslugify(_heading));
		var $body = $('<div>').addClass('panel-body flex-grow');
		var $table = $('<table>').addClass('table table-condensed table-striped');
		$panel.append($heading);
		if (typeof _data == 'object') {
			$panel.append($body.append($table));
			for (_key in _data) {
				var $tr = $('<tr>');
				$tr.append($('<th>').text(deslugify(_key)));
				$tr.append($('<td>').text(_data[_key]));
				$table.append($tr);
			}
		}
		$container.append($panel);
		return $container;
	}

	var deslugify = function(slug) {
		return slug.replace(/(\_[a-z])/g, function($1) {
			return $1.toUpperCase().replace('_', ' ');
		}).trim().toProperCase();
	};

	return $(this).each(function() {
		var $node = $(this);
		$node.append(render(data, false, 0, 1, $node));
		$('#_shards').tab('show');
	});

};

$.fn.eventsource = function(options, callback) {
	var defaults, opts, basereq, token, index, type, source, buildreq, notifyreq, $progressbar, target, _data, xhr;
	defaults = {
		log : false,
		type : undefined,
		index : undefined,
		onProgress : null,
		cancel : false
	};
	opts = $.extend(true, {}, defaults, options);
	callback = callback || false;
	basereq = "/report/ajax/";
	target = this;

	var init = function(index, type) {
		var buildreq = basereq + "build?index=" + index + "&type=" + type + "&token=" + token;
		var notifyreq = basereq + "notify?index=" + index + "&type=" + type + "&token=" + token;

		xhr = $.getJSON(buildreq, function(data) {
			if (data.message !== undefined) {
				if (opts.log) {
					log(data.message);
				}
			}
		}).fail(function() {
			var msg = {
				data : {
					outcome : 0,
					message : 'Operation failed. Timeout?',
					progress : 100
				}
			};
			if (opts.log) {
				log(msg);
			}
			update(msg);
		}).always(function() {
			setTimeout(function() {
				killbar();
				if (source.readyState != 2) {
					source.close();
					if (callback) {
						callback(target);
					}
				}
			}, 1000);
		});
		listen(notifyreq);
	};

	var listen = function(url) {
		source = new EventSource(url);

		source.addEventListener("message", function(e) {
			if (opts.log) {
				log(e);
			}
			update(e);
		}, false);

		source.addEventListener("notice", function(e) {
			if (opts.log) {
				log(e);
			}
			update(e);
		}, false);

		source.addEventListener("success", function(e) {
			if (opts.log) {
				log(e);
			}
			update(e);
		}, false);

		source.addEventListener("wait", function(e) {
			if (opts.log) {
				// log(e);
			}
		}, false);

		source.addEventListener("error", function(e) {
			if (e.target.readyState == EventSource.CLOSED) {
				source.close();
				if (opts.log) {
					log('Error: Exit.');
					log(e);
				}
				update(e);
			}
		}, false);

		source.addEventListener("fail", function(e) {
			logMessage(e);
			if (e.target.readyState == EventSource.CLOSED) {
				source.close();
				if (opts.log) {
					log('Fail: Exit.');
					log(e);
				}
				update(e);
			}
		}, false);

	};

	var is_json = function(str) {
		try {
			JSON.parse(str);
		} catch (e) {
			return false;
		}
		return true;
	};

	var log = function(msg) {
		var text, data, output;
		text = [];
		data = [];
		if (typeof msg === "string") {
			text.push(msg);
		} else if (typeof msg == 'object') {
			if (msg.lastEventId !== undefined) {
				text.push(msg.lastEventId);
			}
			if (msg.data !== undefined) {
				if (typeof msg.data == 'string') {
					if (is_json(msg.data)) {
						data.push(JSON.parse(msg.data));
					}
					text.push(msg.data);
				} else {
					data.push(msg.data);
					text.push(JSON.stringify(msg.data));
				}
			}
		}
		if (data.length) {
			data.forEach(function(d) {
				console.dir(d);
			});
		} else if (text.length > 0) {
			console.log(text.join(' - '));
		}
	};

	var gettoken = function(length) {
		var text = "";
		var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		for (var i = 0; i < length; i++) {
			text += possible.charAt(Math.floor(Math.random() * possible.length));
		}
		return text;
	};

	var buildbar = function(type) {
		var $wrap = $('<div>').addClass('pbar-wrapper');
		var $outer = $('<div>').prop('id', 'pbar-' + type).addClass('progress');
		var $text = $('<span>').addClass('progress-value').text('0%');
		var $bar = $('<div role="progressbar">').addClass('progress-bar progress-bar-info progress-bar-striped active').prop({
			'aria-valuenow' : 0,
			'aria-valuemin' : 0,
			'aria-valuemax' : 100
		}).css('width', '0%');
		$outer.append($bar);
		$outer.append($text);
		$wrap.append($outer);
		return $wrap;
	};

	var killbar = function(fade) {
		fade = fade || true;
		if ($progressbar !== undefined && ($progressbar.length > 0)) {
			if (fade) {
				$progressbar.fadeOut(1000, function() {
					$(this).remove();
				});
			} else {
				$progressbar.remove();
			}
		}
	};

	var update = function(response) {
		var data = false;
		if (typeof response == 'object') {
			if (response.data !== undefined) {
				data = response.data;
				if (typeof data == 'string') {
					if (is_json(data)) {
						data = JSON.parse(data);
					} else {
						data = false;
					}
				}
				if (data) {
					if (opts.onProgress) {
						opts.onProgress(data);
					}
					if ($progressbar !== undefined && ($progressbar.length > 0)) {
						if (typeof data.progress !== undefined) {
							$progressbar.find('.progress-bar').prop('aria-valuenow', data.progress).css('width', data.progress + '%');
						}
						if (typeof data.message !== undefined) {
							var message = typeof data.message == 'string' ? data.message : data.message.message;
							$progressbar.find('.progress-value').text(message);
						}
					}
				}
			}
		}
	};

	_data = function() {
		this.cancel = function(cb) {
			if (source !== undefined) {
				source.close();
				xhr.abort();
				killbar();
			}
			if (cb !== undefined) {
				cb(target);
			}
		}
	};

	return $(this).each(function() {
		if (!!window.EventSource) {
			var $btn = $(this);
			token = gettoken(16);
			type = opts.type !== undefined ? opts.type : $btn.data('type');
			index = opts.index !== undefined ? opts.type : $btn.data('index');
			if (index && type) {
				$progressbar = buildbar(type);
				$btn.closest('.es-wrap').find('.pbar-wrapper').remove();
				$btn.closest('.es-wrap').find('.panel-body').append($progressbar);
				$.data($btn.get(0), 'eventsource', new _data());
				init(index, type);
			}
		} else {
			$("#notSupported").show();
		}
	});

};

$.fn.fieldconditions = function(options) {
	var defaults, opts;
	defaults = {
		events : 'change',
		target : null,
		callback : null,
		ajaxUrl : false,
		data : false
	};
	opts = $.extend(true, {}, defaults, options);

	return $(this).each(function() {
		var el = this;
		if (opts.events) {
			$(el).on(opts.events, function(e) {
				if (opts.ajaxUrl) {
					if (typeof opts.ajaxUrl == 'function') {
						var ajaxUrl = opts.ajaxUrl($(el));
					} else {
						var ajaxUrl = opts.ajaxUrl;
					}
					$.getJSON(ajaxUrl, function(response) {
						if (opts.callback) {
							opts.callback.call(el, opts.target, response, opts.data, e);
						}
					});
				} else {
					if (opts.callback) {
						opts.callback.call(el, opts.target, opts.data, null, e);
					}
				}
			});
		}
	});
};

$.fn.gridwrap = function(options) {
	var defaults, opts;
	defaults = {
		'class' : '.row',
		'target' : '.form-group'
	};
	opts = $.extend(true, {}, defaults, options);
	return $(this).each(function() {
		var $parent = $(this);
		$parent.addClass(opts.class).find(opts.target).each(function() {
			var $form_group = $(this);
			var tClass = opts.target.replace(/\./i, ' ');
			var $column = $('<div>').addClass($form_group.prop('class')).removeClass(tClass);
			$form_group.removeClass().addClass(tClass).appendTo($column);
			$parent.append($column);
		});
	});
};

$.fn.initChosen = function(options) {
	var defaults, opts;

	defaults = {
		placeholder : false,
		max : Infinity,
		empty : false,
		resize : false,
		force : false
	};
	opts = $.extend(true, {}, defaults, options);

	if ($.isFunction($.fn.chosen)) {
		return $(this).each(function() {
			var $control = $(this);
			var val = $control.val();
			var placeholder = opts.placeholder;
			var resize = function(e) {
				try {
					$control.parent().find('.chosen-container').each(function() {
						$(this).attr('style', 'width: 100%');
					});
				} catch (e) {

				}
			};
			var implode = function(e) {
				if ($control.is('.form-control[multiple]')) {
					if ($.isArray($control.val())) {
						var val = $control.val();
						$control.val(val[0]);
					}
				}
			};
			if (opts.resize) {
				$(window).on('resize', resize);
			}

			if (!placeholder) {
				if (!!$control.prop('placeholder')) {
					placeholder = $control.prop('placeholder');
				}
				if (!!$control.prop('data-placeholder')) {
					placeholder = $control.prop('data-placeholder');
				}
				if (!placeholder) {
					placeholder = 'Select Option';
				}
			}
			$control.prop('multiple', 'multiple');
			if ($control.parent().find('.chosen-container').length > 0) {
				$control.chosen('destroy');
			}
			if (val !== undefined && !$.isArray(val)) {
				val = [ val ];
			}
			if (!$.isArray(val) || (val.length == 1 && val[0] == "")) {
				$control.prop("selected", false);
				$control.val([]);
			} else {
				$control.enable();
			}
			if (opts.empty) {
				$control.empty();
			}
			$control.chosen({
				'disable_search_threshold' : 2,
				'max_selected_options' : opts.max,
				'placeholder_text_multiple' : placeholder,
				'allow_single_deselect' : true
			});
			$control.closest('FORM').on('reset', function() {
				$control.val([]).prop('selected', false).trigger('change').trigger('chosen:updated');
			});
			if (opts.max == 1) {
				$control.closest('FORM').on('submit', implode);
			}
			if (opts.force) {
				$control.after($('<input>').prop({
					'type' : 'hidden',
					'name' : $control.prop('name'),
					'class' : 'chosen-force'
				}).val($control.val()));
				$control.on('change', function(e, p) {
					var val = $(this).val();
					$(this).nextAll('.chosen-force').val(val);
				});
			}
		});
	}
};

$.fn.initDaterange = function(options) {
	var defaults, opts;

	defaults = {
		autoUpdateInput : true,
	};
	opts = $.extend(true, {}, defaults, options);

	if (typeof $.fn.daterangepicker == 'function' && typeof moment == 'function') {
		return $(this).each(function() {
			var $control = $(this), $picker = $control.closest('.form-group-daterange'), args;
			if ($picker.data('daterangepicker') !== undefined) {
				// $picker.data('daterangepicker').remove();
			}
			function resize(e) {
				var l = $control.val().length;
				$control.width(((l + 1) * 6) + 'px');
			}
			$control.on('change', resize);
			$(window).on('resize', resize);
			function cb(start, end) {
				start = start || null;
				end = end || null;
				if (start && end) {
					$control.val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
				}
				$control.trigger('change');
			}
			args = {
				autoUpdateInput : opts.autoUpdateInput,
				ranges : {
					'Today' : [ moment(), moment() ],
					'Yesterday' : [ moment().subtract(1, 'days'), moment().subtract(1, 'days') ],
					'Last 7 Days' : [ moment().subtract(6, 'days'), moment() ],
					'Last 30 Days' : [ moment().subtract(29, 'days'), moment() ],
					'This Month' : [ moment().startOf('month'), moment().endOf('month') ],
					'Last Month' : [ moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month') ]
				}
			};
			if (!opts.autoUpdateInput) {
				$control.val('').trigger('change');
				args.locale = {
					cancelLabel : 'Clear'
				};
				$picker.daterangepicker(args);
				$picker.on('apply.daterangepicker', function(ev, picker) {
					$(this).find(':input').val(picker.startDate.format('MMMM D, YYYY') + ' - ' + picker.endDate.format('MMMM D, YYYY')).trigger('change');
				});

				$picker.on('cancel.daterangepicker', function(ev, picker) {
					$(this).find(':input').val('').trigger('change');
				});
			} else {
				if (!$control.val()) {
					cb(moment().subtract(29, 'days'), moment());
				} else {
					cb();
				}
				$picker.daterangepicker(args, cb);
			}
		});
	}
};

$.fn.collapseMenu = function(options) {
	var defaults, opts;

	defaults = {
		remember : true,
		init : true
	};
	opts = $.extend(true, {}, defaults, options);

	return $(this).each(function() {
		if (opts.init) {
			if (this.id) {
				var key = this.id + "_collapse_folded";
				var folded = Cookies.get(key);
				if (folded) {
					$(this).closest('BODY').addClass('folded');
				} else {
					$(this).closest('BODY').removeClass('folded');
				}
			}
		}
		$(this).on('click', function(e) {
			$(this).closest('BODY').toggleClass('folded');
			if (opts.remember && this.id) {
				var key = this.id + "_collapse_folded";
				Cookies.set(key, $(this).closest('BODY').hasClass('folded'), {
					expires : 365
				});
			}
		});

	});

}

$(function() {
	// COLLAPSE MENU
	$("#collapse-menu").collapseMenu();

	// ADD SLIDEDOWN ANIMATION TO DROPDOWN //
	$('BODY:not(.folded) .navbar-nav .dropdown').on('show.bs.dropdown', function(e) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideDown();
	});

	// ADD SLIDEUP ANIMATION TO DROPDOWN //
	$('BODY:not(.folded) .navbar-nav .dropdown').on('hide.bs.dropdown', function(e) {
		$(this).find('.dropdown-menu').first().stop(true, true).slideUp();
	});

	// SELECT ALL CHECKBOXES
	var $enabled = $("TABLE.sel TD .checkbox INPUT[type=checkbox][disabled!=disabled]");
	if ($enabled.length == 0) {
		$('#batchsubmitbutton').prop('disabled', 'disabled');
	}
	$('TABLE.sel TH .selall').click(function() {
		$enabled.prop("checked", $(this).prop("checked"));
	});

	// EXPORT LEADS CLICK HANDLER
	$('#exportLeads').on('click', function(e) {
		e.preventDefault();
		var url = $(this).prop('href');
		var params = $('#leadFilterForm').serialize();
		window.location.href = url + '?' + params;
		return false;
	});

	// LEADS NAME FILTER
	if (typeof $.fn.typeahead == 'function') {
		$('.description.typeahead').typeahead({
			hint : true,
			highlight : true,
			minLength : 2
		}, {
			source : function(query, sync, process) {
				return $.getJSON('/lead/ajax/name', {
					query : query
				}, function(data) {
					return process(data);
				});
			}
		}).on('typeahead:change', function(e) {
			$(this).trigger('change');
		}).on('typeahead:asyncrequest', function(e) {
			$(this).addClass('loading');
		}).on('typeahead:asyncreceive typeahead:asynccancel', function(e) {
			$(this).removeClass('loading');
		});
	}

	// IMPORT LEADS HINTER
	$('#leadimport .import-fields SELECT').on(
			'change',
			function(e) {
				$(this).closest('.form-group').find('.desc').remove();
				var val = $(this).val();
				if ($.isArray(val)) {
					val = val[0];
				}
				if (val === null || val === "") {
					val = null;
				} else if (val.length == 0) {
					val = null;
				}
				var text = $(this).find('OPTION:selected').text();
				var match = $(this).data('match');

				if (val === "Question") {
					$(this).removeClass('custom match ignore').addClass("new").prop('title', 'You want this to be a new field.').after(
							'<span class="desc new">New Field</span>');
				} else if (val !== null && (val != match)) {
					$(this).removeClass('match new ignore').addClass('custom').prop('title', 'You have custom mapped this field.').after(
							'<span class="desc custom">Custom Field</span>');
				} else if (val !== null && (val == match)) {
					$(this).removeClass('custom new ignore').addClass('match').prop('title', 'This field has been automatically mapped.').after(
							'<span class="desc match">Matched Field</span>');
				} else if (text === "Ignore Field" && val === null) {
					$(this).removeClass('custom match new').addClass("ignore").prop('title', 'You have ignored this field.').after(
							'<span class="desc ignore">Ignored Field</span>');
				} else if (val === null) {
					$(this).removeClass('custom match ignore new').after('<span class="desc ignore">&nbsp;</span>');
				}
			}).trigger('change');

	// TABLE COLLAPSE LABELS
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

	// TEXTAREA AUTORESIZE
	autosize($('textarea.autoresize'));
});