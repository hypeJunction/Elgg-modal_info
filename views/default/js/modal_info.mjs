import Ajax from 'elgg/Ajax';
import $ from 'jquery';
import lightbox from 'elgg/lightbox';

const $modal = $('#modal-info');
if ($modal.length === 0) {
	// Nothing to show
} else {
	lightbox.open({
		html: $modal.html(),
		width: $modal.data('width') || 600,
		height: $modal.data('height') || 600,
	});

	$(document).on('click', '.modal-info-dismiss', function (e) {
		e.preventDefault();
		const $elem = $(this);
		const ajax = new Ajax();

		lightbox.close();
		ajax.action($elem.attr('href'));
	});
}
