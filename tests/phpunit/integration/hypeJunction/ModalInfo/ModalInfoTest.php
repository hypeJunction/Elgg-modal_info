<?php

namespace hypeJunction\ModalInfo;

use Elgg\IntegrationTestCase;
use ElggObject;
use ElggUser;

class ModalInfoTest extends IntegrationTestCase {

	/**
	 * @var ElggUser
	 */
	protected $admin;

	/**
	 * @var ElggUser
	 */
	protected $user;

	public function up() {
		$this->admin = $this->getAdmin();
		_elgg_services()->session_manager->setLoggedInUser($this->admin);

		$this->user = $this->createUser();
	}

	public function down() {
		if ($this->user instanceof ElggUser) {
			$this->user->delete();
		}

		_elgg_services()->session_manager->removeLoggedInUser();
	}

	/**
	 * Test that object/modal_info subtype can be created and loaded.
	 */
	public function testEntityClassMapping() {
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Test Modal';
		$this->assertTrue($entity->save() !== false);

		$loaded = get_entity($entity->guid);
		$this->assertInstanceOf(ElggObject::class, $loaded);
		$this->assertEquals('modal_info', $loaded->getSubtype());

		$entity->delete();
	}

	/**
	 * Test creating a modal_info entity with all metadata fields.
	 */
	public function testCreateModalInfo() {
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Welcome Modal';
		$entity->description = '<p>Welcome to the site!</p>';
		$entity->page_urls = ['/activity', '/dashboard'];
		$entity->all_pages = false;
		$entity->width = 800;
		$entity->height = 500;
		$entity->show_once = true;
		$entity->can_dismiss = true;

		$this->assertTrue($entity->save() !== false);

		// Reload from DB
		_elgg_services()->entityCache->delete($entity->guid);
		$loaded = get_entity($entity->guid);

		$this->assertNotNull($loaded);
		$this->assertEquals('Welcome Modal', $loaded->title);
		$this->assertEquals('<p>Welcome to the site!</p>', $loaded->description);
		$this->assertEquals(800, $loaded->width);
		$this->assertEquals(500, $loaded->height);
		$this->assertTrue((bool) $loaded->show_once);
		$this->assertTrue((bool) $loaded->can_dismiss);

		// page_urls is stored as multiple metadata values
		$page_urls = (array) $loaded->page_urls;
		$this->assertContains('/activity', $page_urls);
		$this->assertContains('/dashboard', $page_urls);

		$entity->delete();
	}

	/**
	 * Test the modal_info/edit action creates a new entity.
	 */
	public function testEditAction() {
		$site = elgg_get_site_entity();

		$response = $this->executeAction('modal_info/edit', [
			'title' => 'Action Created Modal',
			'description' => 'Created via action',
			'container_guid' => $site->guid,
			'access_id' => ACCESS_PUBLIC,
			'width' => 700,
			'height' => 400,
			'show_once' => 1,
			'can_dismiss' => 1,
			'all_pages' => 1,
		]);

		$this->assertInstanceOf(\Elgg\Http\OkResponse::class, $response);

		// Find the created entity
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => 'modal_info',
			'metadata_name_value_pairs' => [
				['name' => 'title', 'value' => 'Action Created Modal'],
			],
			'limit' => 1,
		]);

		$this->assertNotEmpty($entities);
		$entity = $entities[0];

		$this->assertEquals('Action Created Modal', $entity->title);
		$this->assertEquals('Created via action', $entity->description);
		$this->assertEquals(700, $entity->width);
		$this->assertEquals(400, $entity->height);
		$this->assertTrue((bool) $entity->all_pages);
		$this->assertTrue((bool) $entity->show_once);
		$this->assertTrue((bool) $entity->can_dismiss);

		$entity->delete();
	}

	/**
	 * Test the modal_info/edit action updates an existing entity.
	 */
	public function testEditActionUpdatesExisting() {
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Original Title';
		$entity->description = 'Original Description';
		$entity->width = 600;
		$entity->height = 600;
		$entity->save();

		$response = $this->executeAction('modal_info/edit', [
			'guid' => $entity->guid,
			'title' => 'Updated Title',
			'description' => 'Updated Description',
			'access_id' => ACCESS_PUBLIC,
			'width' => 900,
			'height' => 450,
			'show_once' => 1,
			'can_dismiss' => 0,
		]);

		$this->assertInstanceOf(\Elgg\Http\OkResponse::class, $response);

		// Reload
		_elgg_services()->entityCache->delete($entity->guid);
		$loaded = get_entity($entity->guid);

		$this->assertEquals('Updated Title', $loaded->title);
		$this->assertEquals('Updated Description', $loaded->description);
		$this->assertEquals(900, $loaded->width);
		$this->assertEquals(450, $loaded->height);

		$entity->delete();
	}

	/**
	 * Test the modal_info/dismiss action creates a viewed relationship.
	 */
	public function testDismissAction() {
		// Create modal as admin
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Dismissable Modal';
		$entity->can_dismiss = true;
		$entity->save();

		// Login as regular user
		_elgg_services()->session_manager->setLoggedInUser($this->user);

		$response = $this->executeAction('modal_info/dismiss', [
			'guid' => $entity->guid,
		]);

		$this->assertInstanceOf(\Elgg\Http\OkResponse::class, $response);

		// Assert relationship exists
		$this->assertTrue(
			$this->user->hasRelationship($entity->guid, 'viewed'),
			'Expected "viewed" relationship between user and modal'
		);

		// Cleanup
		_elgg_services()->session_manager->setLoggedInUser($this->admin);
		$entity->delete();
	}

	/**
	 * Test that entity URL is set correctly for modal_info objects.
	 */
	public function testEntityUrl() {
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'URL Test Modal';
		$entity->save();

		$url = $entity->getURL();
		$this->assertStringContainsString("modal_info/view/{$entity->guid}", $url);

		$entity->delete();
	}

	/**
	 * Test that non-admin users cannot access admin-gatekeepered routes.
	 */
	public function testAdminGatekeeper() {
		// Login as non-admin user
		_elgg_services()->session_manager->setLoggedInUser($this->user);

		// The routes are defined with AdminGatekeeper middleware.
		// Verify the user is not an admin.
		$this->assertFalse($this->user->isAdmin());

		// Verify route definitions have AdminGatekeeper middleware
		$plugin_config = include dirname(__DIR__, 5) . '/elgg-plugin.php';
		$routes = $plugin_config['routes'] ?? [];

		foreach ($routes as $name => $route) {
			$middleware = $route['middleware'] ?? [];
			$this->assertContains(
				\Elgg\Router\Middleware\AdminGatekeeper::class,
				$middleware,
				"Route '{$name}' should have AdminGatekeeper middleware"
			);
		}

		// Restore admin session
		_elgg_services()->session_manager->setLoggedInUser($this->admin);
	}

	/**
	 * Test that admin can edit modal_info entities and non-admin cannot.
	 */
	public function testPermissions() {
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Permission Test Modal';
		$entity->save();

		// Admin can edit
		$this->assertTrue($entity->canEdit($this->admin->guid));

		// Non-admin cannot edit (not the owner)
		$this->assertFalse($entity->canEdit($this->user->guid));

		$entity->delete();
	}
}
