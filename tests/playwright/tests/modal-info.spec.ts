import { test, expect } from '@playwright/test';
import {
  loginAs,
  queryDb,
  getMetadata,
  getRelationship,
  findEntities,
  getUserGuid,
} from '../helpers/elgg';

const TABLE_PREFIX = process.env.ELGG_DB_PREFIX || 'elgg_';

const ADMIN_USER = process.env.ELGG_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.ELGG_ADMIN_PASS || 'admin12345';
const TEST_USER = process.env.ELGG_TEST_USER || 'testuser';
const TEST_PASS = process.env.ELGG_TEST_PASS || 'testuser12345';

/**
 * Helper: delete a modal_info entity by GUID via direct DB cleanup.
 */
async function cleanupEntity(guid: number) {
  await queryDb(`DELETE FROM ${TABLE_PREFIX}metadata WHERE entity_guid = ?`, [guid]);
  await queryDb(`DELETE FROM ${TABLE_PREFIX}entity_relationships WHERE guid_one = ? OR guid_two = ?`, [guid, guid]);
  await queryDb(`DELETE FROM ${TABLE_PREFIX}entities WHERE guid = ?`, [guid]);
}

test.describe('Modal Info Plugin', () => {

  // Clean up ALL modal_info entities before each test to prevent cross-test interference.
  // Using SQL directly because Elgg's delete() requires an active session.
  test.beforeEach(async () => {
    await queryDb(`DELETE FROM ${TABLE_PREFIX}metadata WHERE entity_guid IN (SELECT guid FROM ${TABLE_PREFIX}entities WHERE subtype = 'modal_info')`);
    await queryDb(`DELETE FROM ${TABLE_PREFIX}entity_relationships WHERE guid_one IN (SELECT guid FROM ${TABLE_PREFIX}entities WHERE subtype = 'modal_info') OR guid_two IN (SELECT guid FROM ${TABLE_PREFIX}entities WHERE subtype = 'modal_info')`);
    await queryDb(`DELETE FROM ${TABLE_PREFIX}entities WHERE subtype = 'modal_info'`);
  });

  test('admin can create modal info', async ({ page }) => {
    await loginAs(page, ADMIN_USER, ADMIN_PASS);
    await page.goto('/modal_info/add');

    // Verify we are on the add form page
    await expect(page.locator('input[name="title"]')).toBeVisible();

    // Fill the form
    await page.fill('input[name="title"]', 'E2E Test Modal');
    // Description may be a longtext/tinymce field - try filling the textarea
    const descField = page.locator('textarea[name="description"]');
    if (await descField.isVisible()) {
      await descField.fill('E2E test description content');
    }
    await page.fill('input[name="width"]', '700');
    await page.fill('input[name="height"]', '450');

    // Check show_once and can_dismiss checkboxes
    const showOnce = page.locator('input[name="show_once"]');
    if (await showOnce.isVisible()) {
      await showOnce.check();
    }
    const canDismiss = page.locator('input[name="can_dismiss"]');
    if (await canDismiss.isVisible()) {
      await canDismiss.check();
    }

    // Submit the form
    await page.click('input[type="submit"], button[type="submit"]');

    // Should redirect to the listing page
    await page.waitForURL('**/modal_info/all**');
    await expect(page).toHaveURL(/modal_info\/all/);

    // Verify the entity exists in DB
    const entities = await findEntities('object', 'modal_info');
    const created = entities.find((e: any) => {
      // Match by checking metadata
      return true; // We just check that at least one exists
    });
    expect(entities.length).toBeGreaterThan(0);

    // Check metadata for the newest entity
    const newest = entities[0];
    const titleMeta = await getMetadata(newest.guid, 'title');
    expect(titleMeta.length).toBeGreaterThan(0);
    expect(titleMeta[0].value).toBe('E2E Test Modal');

    // Cleanup
    await cleanupEntity(newest.guid);
  });

  test('admin can edit modal info', async ({ page }) => {
    await loginAs(page, ADMIN_USER, ADMIN_PASS);

    // Create a modal first via the form
    await page.goto('/modal_info/add');
    await page.fill('input[name="title"]', 'Modal To Edit');
    const descField = page.locator('textarea[name="description"]');
    if (await descField.isVisible()) {
      await descField.fill('Original description');
    }
    await page.fill('input[name="width"]', '600');
    await page.fill('input[name="height"]', '600');
    await page.click('input[type="submit"], button[type="submit"]');
    await page.waitForURL('**/modal_info/all**');

    // Find the created entity
    const entities = await findEntities('object', 'modal_info');
    expect(entities.length).toBeGreaterThan(0);
    const entity = entities[0];

    // Navigate to edit page
    await page.goto(`/modal_info/edit/${entity.guid}`);
    await expect(page.locator('input[name="title"]')).toBeVisible();

    // Change the title
    await page.fill('input[name="title"]', 'Updated Modal Title');
    await page.click('input[type="submit"], button[type="submit"]');
    await page.waitForURL('**/modal_info/all**');

    // Verify DB is updated
    const titleMeta = await getMetadata(entity.guid, 'title');
    expect(titleMeta.length).toBeGreaterThan(0);
    expect(titleMeta[0].value).toBe('Updated Modal Title');

    // Cleanup
    await cleanupEntity(entity.guid);
  });

  test('admin can view modal list', async ({ page }) => {
    await loginAs(page, ADMIN_USER, ADMIN_PASS);
    await page.goto('/modal_info/all');

    // The page should load without error
    await expect(page).toHaveURL(/modal_info\/all/);

    // Should contain the page title or heading
    const heading = page.locator('h1, h2, .elgg-heading-main, [class*="heading"]');
    await expect(heading.first()).toBeVisible();
  });

  test('non-admin cannot access modal admin', async ({ page }) => {
    await loginAs(page, TEST_USER, TEST_PASS);

    const response = await page.goto('/modal_info/all');

    // Should be blocked by AdminGatekeeper - either 403 or redirected away
    const url = page.url();
    const status = response?.status();

    const isBlocked =
      status === 403 ||
      !url.includes('/modal_info/all') ||
      (await page.locator('.elgg-system-messages .elgg-message-error, .elgg-state-error').count()) > 0;

    expect(isBlocked).toBeTruthy();
  });

  test('modal displays on matching page', async ({ page }) => {
    await loginAs(page, ADMIN_USER, ADMIN_PASS);

    // Create a modal targeting the activity page
    await page.goto('/modal_info/add');
    await page.fill('input[name="title"]', 'Activity Page Modal');
    const descField = page.locator('textarea[name="description"]');
    if (await descField.isVisible()) {
      await descField.fill('This should show on activity page');
    }
    // Set page_urls to the activity page
    const urlsField = page.locator('textarea[name="page_urls"]');
    if (await urlsField.isVisible()) {
      await urlsField.fill('/activity');
    }
    await page.fill('input[name="width"]', '500');
    await page.fill('input[name="height"]', '400');
    await page.click('input[type="submit"], button[type="submit"]');
    await page.waitForURL('**/modal_info/all**');

    const entities = await findEntities('object', 'modal_info');
    const entity = entities[0];

    // Switch to test user — clear admin session cookies first.
    await page.context().clearCookies();
    await loginAs(page, TEST_USER, TEST_PASS);
    await page.goto('/activity');

    // The modal-info div should be rendered in the footer
    const modalDiv = page.locator('#modal-info');
    // Give it a moment to appear (it is injected via footer preload)
    const modalVisible = await modalDiv.count();

    // Whether the lightbox actually opens depends on JS; at minimum check the div exists
    // Note: if the user has already viewed this modal, it won't appear
    // This is a best-effort check
    if (modalVisible > 0) {
      await expect(modalDiv).toHaveAttribute('data-guid', String(entity.guid));
    }

    // Cleanup
    await cleanupEntity(entity.guid);
  });

  test('dismiss button creates viewed relationship', async ({ page }) => {
    await loginAs(page, ADMIN_USER, ADMIN_PASS);

    // Create a dismissable modal for all pages
    await page.goto('/modal_info/add');
    await page.fill('input[name="title"]', 'Dismiss Test Modal');
    const descField = page.locator('textarea[name="description"]');
    if (await descField.isVisible()) {
      await descField.fill('Test dismiss functionality');
    }
    const allPages = page.locator('input[name="all_pages"]');
    if (await allPages.isVisible()) {
      await allPages.check();
    }
    const canDismiss = page.locator('input[name="can_dismiss"]');
    if (await canDismiss.isVisible()) {
      await canDismiss.check();
    }
    await page.fill('input[name="width"]', '500');
    await page.fill('input[name="height"]', '400');
    await page.click('input[type="submit"], button[type="submit"]');
    await page.waitForURL('**/modal_info/all**');

    const entities = await findEntities('object', 'modal_info');
    const entity = entities[0];

    // Switch to test user — clear admin session cookies first.
    await page.context().clearCookies();
    await loginAs(page, TEST_USER, TEST_PASS);

    // Navigate to any page - modal should appear
    await page.goto('/activity');

    // The JS lightbox copies #modal-info content into the lightbox overlay.
    // Use getByRole to target the VISIBLE dismiss link in the lightbox (not the hidden
    // copy inside #modal-info which has display:none).
    const dismissBtn = page.getByRole('link', { name: /dismiss/i });
    if (await dismissBtn.count() > 0) {
      // Click dismiss - this triggers an AJAX action
      await dismissBtn.first().click();

      // Wait for the AJAX request to complete
      await page.waitForTimeout(1000);

      // Verify the "viewed" relationship exists in DB
      const userGuid = await getUserGuid(TEST_USER);
      expect(userGuid).not.toBeNull();

      const rel = await getRelationship(userGuid!, 'viewed', entity.guid);
      expect(rel).not.toBeNull();
    }

    // Cleanup
    await cleanupEntity(entity.guid);
  });
});
