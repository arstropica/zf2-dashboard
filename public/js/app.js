/**
 * Application JS
 */

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
			$(this).removeClass('custom match ignore').addClass("new").prop(
					'title', 'You want this to be a new field.').after(
					'<span class="desc new">New Field</span>');
		} else if (val !== "" && val != match) {
			$(this).removeClass('match new ignore').addClass('custom').prop(
					'title', 'You have custom mapped this field.').after(
					'<span class="desc custom">Custom Field</span>');
		} else if (val !== "" && val == match) {
			$(this).removeClass('custom new ignore').addClass('match').prop(
					'title', 'This field has been automatically mapped.')
					.after(
							'<span class="desc match">Matched Field</span>');
		} else if (val === "ignore") {
			$(this).removeClass('custom match new').addClass("ignore").prop(
					'title', 'You have ignored this field.').after(
					'<span class="desc ignore">Ignored Field</span>');
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