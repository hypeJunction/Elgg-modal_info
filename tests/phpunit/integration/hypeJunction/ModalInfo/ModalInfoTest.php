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
		elgg_get_session()->setLoggedInUser($this->admin);

		$this->user = $this->createUser();
	}

	public function down() {
		if ($this->user instanceof ElggUser) {
			$this->user->delete();
		}

		elgg_get_session()->removeLoggedInUser();
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
	 * Test creating a modal_info entity via direct save (action file test).
	 */
	public function testDirectEntityCreation() {
		$site = elgg_get_site_entity();

		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = $site->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Action Created Modal';
		$entity->description = 'Created via test';
		$entity->width = 700;
		$entity->height = 400;
		$entity->show_once = true;
		$entity->can_dismiss = true;
		$entity->all_pages = true;
		$this->assertTrue($entity->save() !== false);

		// Find the created entity
		$entities = elgg_get_entities([
			'type' => 'object',
			'subtype' => 'modal_info',
			'owner_guid' => $this->admin->guid,
			'limit' => 1,
			'sort_by' => [
				'property' => 'time_created',
				'direction' => 'desc',
			],
		]);

		$this->assertNotEmpty($entities);
		$loaded = $entities[0];

		$this->assertEquals('Action Created Modal', $loaded->title);
		$this->assertEquals(700, $loaded->width);
		$this->assertEquals(400, $loaded->height);
		$this->assertTrue((bool) $loaded->all_pages);
		$this->assertTrue((bool) $loaded->show_once);
		$this->assertTrue((bool) $loaded->can_dismiss);

		$entity->delete();
	}

	/**
	 * Test updating a modal_info entity.
	 */
	public function testEntityUpdate() {
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

		$entity->title = 'Updated Title';
		$entity->description = 'Updated Description';
		$entity->width = 900;
		$entity->height = 450;
		$entity->save();

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
	 * Test the viewed relationship for modal dismissal.
	 */
	public function testDismissRelationship() {
		// Create modal as admin
		$entity = new ElggObject();
		$entity->setSubtype('modal_info');
		$entity->owner_guid = $this->admin->guid;
		$entity->container_guid = elgg_get_site_entity()->guid;
		$entity->access_id = ACCESS_PUBLIC;
		$entity->title = 'Dismissable Modal';
		$entity->can_dismiss = true;
		$entity->save();

		// Create viewed relationship (simulating what dismiss action does)
		$this->assertTrue(
			$this->user->addRelationship($entity->guid, 'viewed'),
			'Should be able to create viewed relationship'
		);

		// Assert relationship exists
		$this->assertTrue(
			$this->user->hasRelationship($entity->guid, 'viewed'),
			'Expected "viewed" relationship between user and modal'
		);

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
		elgg_get_session()->setLoggedInUser($this->user);

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
		elgg_get_session()->setLoggedInUser($this->admin);
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
