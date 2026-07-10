<?php

namespace PrototyperGroups;

use Elgg\IntegrationTestCase;
use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;
use hypeJunction\Prototyper\Groups\Upgrade\MigratePrototypesToJson;

/**
 * Regression tests for the serialize()->json_encode() data migration.
 *
 * Pins commit 72baa04: MigratePrototypesToJson became an AsynchronousUpgrade
 * (Elgg\Upgrade\Batch became abstract in 6.x) with run(Result, $offset): Result,
 * and its run() converts serialized settings to JSON while leaving already-JSON
 * values untouched, using allowed_classes:false to block object injection.
 */
class MigratePrototypesToJsonTest extends IntegrationTestCase {

	public function up() {
	}

	public function down() {
	}

	/**
	 * @return string
	 */
	public function getPluginID(): string {
		return 'prototyper_group';
	}

	/**
	 * Regression: 72baa04 — the upgrade extends the 6.x/7.x abstract batch
	 * (AsynchronousUpgrade) and exposes the required run signature + version.
	 *
	 * @return void
	 */
	public function testUpgradeExtendsAsynchronousUpgradeWithResultRunSignature(): void {
		$upgrade = new MigratePrototypesToJson();
		$this->assertInstanceOf(AsynchronousUpgrade::class, $upgrade);
		$this->assertSame(2026050401, $upgrade->getVersion());

		$run = new \ReflectionMethod(MigratePrototypesToJson::class, 'run');
		$this->assertSame('Elgg\\Upgrade\\Result', (string) $run->getReturnType(), 'run() must return an Elgg\\Upgrade\\Result');
		$this->assertSame('Elgg\\Upgrade\\Result', (string) $run->getParameters()[0]->getType(), 'first run() param must be a Result');
	}

	/**
	 * Regression: 72baa04 — run() converts a serialized prototype setting into JSON
	 * and leaves an already-JSON setting unchanged. Exercises both branches on real
	 * private_settings rows.
	 *
	 * @return void
	 */
	public function testRunConvertsSerializedSettingToJsonAndLeavesJsonUntouched(): void {
		$plugin = \elgg_get_plugin_from_id('prototyper_group');
		if (!$plugin) {
			$this->markTestSkipped('prototyper_group plugin not installed in test DB.');
			return;
		}

		$legacy = ['legacy' => ['type' => 'text']];
		$already = ['fresh' => ['type' => 'tags']];

		$plugin->setSetting('prototype:legacytest', serialize($legacy));
		$plugin->setSetting('prototype:jsontest', json_encode($already));

		try {
			$result = (new MigratePrototypesToJson())->run(new Result(), 0);
			$this->assertInstanceOf(Result::class, $result);

			$db = elgg()->db;
			$prefix = $db->prefix;
			$rows = $db->getConnection('read')->executeQuery(
				"SELECT name, value FROM {$prefix}private_settings
				 WHERE entity_guid = ? AND name IN ('prototype:legacytest', 'prototype:jsontest')",
				[$plugin->guid]
			)->fetchAllAssociative();

			$values = [];
			foreach ($rows as $row) {
				$values[$row['name']] = $row['value'];
			}

			// serialized -> converted to JSON that decodes back to the same array
			$this->assertArrayHasKey('prototype:legacytest', $values);
			$this->assertSame($legacy, json_decode($values['prototype:legacytest'], true), 'serialized blob must be rewritten as equivalent JSON');
			// already JSON -> byte-for-byte unchanged
			$this->assertSame(json_encode($already), $values['prototype:jsontest'], 'already-JSON setting must be left untouched');
		} finally {
			$plugin->unsetSetting('prototype:legacytest');
			$plugin->unsetSetting('prototype:jsontest');
		}
	}
}
