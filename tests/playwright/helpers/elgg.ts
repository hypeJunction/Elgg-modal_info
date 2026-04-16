import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'localhost',
  port: parseInt(process.env.ELGG_DB_PORT || '3307', 10),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

const TABLE_PREFIX = process.env.ELGG_DB_PREFIX || 'elgg_';

/**
 * Login to Elgg as the given user.
 */
export async function loginAs(page: Page, username: string, password: string): Promise<void> {
  await page.goto('/login');
  // Elgg 4.x renders two login forms (hidden header dropdown + visible sidebar).
  // Target the visible sidebar form to avoid filling the hidden one.
  await page.fill('.elgg-module-aside input[name="username"]', username);
  await page.fill('.elgg-module-aside input[name="password"]', password);
  await page.click('.elgg-module-aside button[type="submit"], .elgg-module-aside input[type="submit"]');
  await page.waitForURL((url) => !url.pathname.includes('/login'));
}

/**
 * Execute a raw SQL query against the Elgg database.
 */
export async function queryDb(sql: string, params: any[] = []): Promise<any[]> {
  const connection = await mysql.createConnection(DB_CONFIG);
  try {
    const [rows] = await connection.execute(sql, params);
    return rows as any[];
  } finally {
    await connection.end();
  }
}

/**
 * Get an entity row by GUID.
 */
export async function getEntity(guid: number): Promise<any | null> {
  const rows = await queryDb(
    `SELECT * FROM ${TABLE_PREFIX}entities WHERE guid = ?`,
    [guid]
  );
  return rows.length > 0 ? rows[0] : null;
}

/**
 * Get metadata value(s) for a given entity and metadata name.
 */
export async function getMetadata(entityGuid: number, name: string): Promise<any[]> {
  return queryDb(
    `SELECT * FROM ${TABLE_PREFIX}metadata WHERE entity_guid = ? AND name = ?`,
    [entityGuid, name]
  );
}

/**
 * Check if a relationship exists between two entities.
 */
export async function getRelationship(
  guidOne: number,
  relationship: string,
  guidTwo: number
): Promise<any | null> {
  const rows = await queryDb(
    `SELECT * FROM ${TABLE_PREFIX}entity_relationships WHERE guid_one = ? AND relationship = ? AND guid_two = ?`,
    [guidOne, relationship, guidTwo]
  );
  return rows.length > 0 ? rows[0] : null;
}

/**
 * Find entities by type/subtype, returns array of entity rows.
 */
export async function findEntities(type: string, subtype: string): Promise<any[]> {
  return queryDb(
    `SELECT * FROM ${TABLE_PREFIX}entities WHERE type = ? AND subtype = ? ORDER BY guid DESC`,
    [type, subtype]
  );
}

/**
 * Get a user GUID by username.
 */
export async function getUserGuid(username: string): Promise<number | null> {
  const rows = await queryDb(
    `SELECT e.guid FROM ${TABLE_PREFIX}entities e
     JOIN ${TABLE_PREFIX}users_entity ue ON e.guid = ue.guid
     WHERE ue.username = ?`,
    [username]
  );
  return rows.length > 0 ? (rows[0] as any).guid : null;
}
