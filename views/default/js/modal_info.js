define(function (require) {

	var Ajax = require('elgg/Ajax');
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
		var ajax = new Ajax();

		lightbox.close();
		ajax.action($elem.attr('href'));
	});

});
