define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');

	var $modal = $('#modal-info');
	if (typeof $.colorbox === 'undefined' || $modal.length === 0) {
		return;
	}

	$.colorbox({
		html: $modal.html(),
		//title: $modal.data('title'),
		width: $modal.data('width') || 600,
		height: $modal.data('height') || 600,
	});

	$(document).on('change', '.modal-info-dismiss', function (e) {
		var $elem = $(this);
		elgg.action($elem.data('action'), {
			data: {
				dismiss: $elem.is(':checked') ? 1 : 0
			}
		});
	});

});