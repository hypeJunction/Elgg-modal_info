define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var lightbox = require('elgg/lightbox');

	var $modal = $('#modal-info');
	if ($modal.length === 0) {
		return;
	}

	lightbox.open({
		html: $modal.html(),
		width: $modal.data('width') || 600,
		height: $modal.data('height') || 600,
	});

	$(document).on('click', '.modal-info-dismiss', function (e) {
		e.preventDefault();
		var $elem = $(this);

		elgg.action($elem.attr('href'), {
			beforeSend: function () {
				lightbox.close();
			}
		});
	});

});
