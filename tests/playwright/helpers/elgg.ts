import { Page } from '@playwright/test';
import mysql from 'mysql2/promise';

const DB_CONFIG = {
  host: process.env.ELGG_DB_HOST || 'db',
  port: Number(process.env.ELGG_DB_PORT || 3306),
  user: process.env.ELGG_DB_USER || 'elgg',
  password: process.env.ELGG_DB_PASS || 'elgg',
  database: process.env.ELGG_DB_NAME || 'elgg',
};

export async function loginAs(page: Page, username: string, password: string = 'testpass123') {
  await page.goto('/login');
  await page.fill('input[name="username"]', username);
  await page.fill('input[name="password"]', password);
  await page.click('button[type="submit"]');
  await page.waitForURL(/\//);
}

export async function queryDb(sql: string, params: any[] = []) {
  const conn = await mysql.createConnection(DB_CONFIG);
  const [rows] = await conn.execute(sql, params);
  await conn.end();
  return rows as any[];
}

export async function getGroupByName(name: string) {
  const rows = await queryDb(
    `SELECT e.guid, e.type, e.subtype, e.owner_guid, e.access_id
     FROM elgg_entities e
     JOIN elgg_groups_entity g ON g.guid = e.guid
     WHERE g.name = ?
     ORDER BY e.guid DESC
     LIMIT 1`,
    [name]
  );
  return rows[0];
}

export async function getPluginSetting(pluginId: string, name: string) {
  const rows = await queryDb(
    `SELECT ps.value
     FROM elgg_private_settings ps
     JOIN elgg_entities e ON e.guid = ps.entity_guid
     JOIN elgg_plugins_entity p ON p.guid = e.guid
     WHERE p.title = ? AND ps.name = ?
     LIMIT 1`,
    [pluginId, 'plugin:user_setting:' + name]
  );
  return rows[0]?.value;
}

export async function getMetadata(entityGuid: number, name: string) {
  return queryDb(
    'SELECT value FROM elgg_metadata WHERE entity_guid = ? AND name = ?',
    [entityGuid, name]
  );
}
