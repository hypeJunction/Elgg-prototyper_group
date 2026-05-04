<?php

namespace hypeJunction\Prototyper\Groups\Upgrade;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * Converts prototype settings stored as serialize() blobs to json_encode() strings.
 *
 * Required because Elgg 5.x migration replaces serialize() with json_encode() in
 * the prototype action and Hooks::getPrototypeFields(). Existing settings remain as
 * PHP-serialized data until this batch runs.
 */
class MigratePrototypesToJson extends AsynchronousUpgrade {

	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int {
		return 2026050401;
	}

	/**
	 * {@inheritdoc}
	 */
	public function needsIncrementOffset(): bool {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function shouldBeSkipped(): bool {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function countItems(): int {
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(Result $result, $offset): Result {
		$plugin = elgg_get_plugin_from_id('prototyper_group');
		if (!$plugin) {
			$result->addSuccesses(1);
			return $result;
		}

		$db = elgg()->db;
		$prefix = $db->prefix;

		// Find all prototype:* settings for this plugin
		$rows = $db->getConnection('read')->executeQuery(
			"SELECT id, name, value FROM {$prefix}private_settings
			 WHERE entity_guid = ? AND name LIKE 'prototype:%'",
			[$plugin->guid]
		)->fetchAllAssociative();

		foreach ($rows as $row) {
			$value = $row['value'];

			// Already JSON — skip
			if ($value !== null && $value !== '' && json_decode($value, true) !== null) {
				continue;
			}

			// Try unserializing
			$unserialized = @unserialize($value);
			if ($unserialized === false && $value !== serialize(false)) {
				// Not valid serialized data either — leave as-is
				continue;
			}

			$json = json_encode($unserialized);
			$db->getConnection('write')->executeStatement(
				"UPDATE {$prefix}private_settings SET value = ? WHERE id = ?",
				[$json, $row['id']]
			);
		}

		$result->addSuccesses(1);
		return $result;
	}
}
