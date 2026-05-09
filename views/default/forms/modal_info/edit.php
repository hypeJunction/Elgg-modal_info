<?php
$entity = elgg_extract('entity', $vars);
$container = elgg_extract('container', $vars);
?>
<div class="elgg-field">
	<label><?php echo elgg_echo('modal_info:title') ?></label>
	<?php
	echo elgg_view('input/text', [
		'name' => 'title',
		'value' => $entity->title ?: '',
		'required' => true,
	]);
	?>
</div>

<div class="elgg-field">
	<label><?php echo elgg_echo('modal_info:description') ?></label>
	<?php
	echo elgg_view('input/longtext', [
		'name' => 'description',
		'value' => $entity->description ?: '',
		'required' => true,
	]);
	?>
</div>

<div class="elgg-field">
	<label><?php echo elgg_echo('modal_info:page_urls') ?></label>
	<?php
	$page_urls = [];
	foreach ((array) $entity->page_urls as $url) {
		$page_urls[] = elgg_normalize_url($url);
	}

	echo elgg_view('input/plaintext', [
		'name' => 'page_urls',
		'value' => implode(PHP_EOL, $page_urls),
	]);
	echo '<label>' . elgg_view('input/checkbox', [
		'name' => 'all_pages',
		'value' => 1,
		'default' => false,
		'checked' => (bool) $entity->all_pages,
	]) . elgg_echo('modal_info:all_pages') . '</label>';
	?>
</div>

<div class="elgg-field">
	<label><?php echo elgg_echo('modal_info:width') ?></label>
	<?php
	echo elgg_view('input/text', [
		'name' => 'width',
		'value' => $entity->width ?: '600',
		'required' => true,
	]);
	?>
</div>

<div class="elgg-field">
	<label><?php echo elgg_echo('modal_info:height') ?></label>
	<?php
	echo elgg_view('input/text', [
		'name' => 'height',
		'value' => $entity->height ?: '600',
		'required' => true,
	]);
	?>
</div>

<div class="elgg-field">
	<?php
	echo '<label>' . elgg_view('input/checkbox', [
		'name' => 'show_once',
		'value' => 1,
		'default' => false,
		'checked' => (bool) $entity->show_once,
	]) . elgg_echo('modal_info:show_once') . '</label>';
	?>
</div>

<div class="elgg-field">
	<?php
	echo '<label>' . elgg_view('input/checkbox', [
		'name' => 'can_dismiss',
		'value' => 1,
		'default' => false,
		'checked' => (bool) $entity->can_dismiss,
	]) . elgg_echo('modal_info:can_dismiss') . '</label>';
	?>
</div>

<div class="elgg-field">
	<label><?php echo elgg_echo('access') ?></label>
	<?php
	echo elgg_view('input/access', [
		'name' => 'access_id',
		'value' => $entity->access_id ?: elgg_get_default_access(),
		'required' => true,
	]);
	?>
</div>

<div class="elgg-field elgg-foot">
	<?php
	echo elgg_view('input/hidden', [
		'name' => 'guid',
		'value' => $entity->guid,
	]);
	echo elgg_view('input/hidden', [
		'name' => 'container_guid',
		'value' => $container->guid,
	]);
	echo elgg_view('input/submit', [
		'value' => elgg_echo('save'),
	]);
	?>
</div>
