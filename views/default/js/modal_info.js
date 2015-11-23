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

	$(document).on('click', '.modal-info-dismiss', function (e) {
		e.preventDefault();
		var $elem = $(this);

		elgg.action($elem.attr('href'), {
			beforeSend: function () {
				$.colorbox.close();
			}
		});
	});

});