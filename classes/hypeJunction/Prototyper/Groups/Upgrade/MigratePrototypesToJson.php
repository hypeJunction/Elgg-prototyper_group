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
	// Elgg only treats a needsIncrementOffset() === false batch as finished when
	// countItems() SHRINKS to zero (Upgrade\Loop::isCompleted). countItems() here
	// is a constant, so returning false made the runner call run() forever — the
	// upgrade never completed, and every later upgrade (including core's
	// MigratePageTop) stayed pending behind it. run() does all of its work in one
	// pass, so let the loop finish on processed >= count instead.
	public function needsIncrementOffset(): bool {
		return true;
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

		// Elgg 4 removed the private_settings table and moved plugin settings into
		// metadata. Querying it threw, the batch was recorded as failed, and Elgg
		// rejected the whole upgrade promise — every upgrade behind it, including
		// core's MigratePageTop, stayed pending. Read the settings where they live.
		foreach ($plugin->getAllMetadata() as $name => $value) {
			if (!str_starts_with((string) $name, 'prototype:') || !is_string($value)) {
				continue;
			}

			// Already JSON — nothing to migrate.
			json_decode($value, true);
			if (json_last_error() === JSON_ERROR_NONE) {
				continue;
			}

			// allowed_classes: false prevents PHP object injection.
			$unserialized = @unserialize($value, ['allowed_classes' => false]);
			if ($unserialized === false && $value !== serialize(false)) {
				// Neither JSON nor serialized — leave it alone.
				continue;
			}

			$plugin->setMetadata($name, json_encode($unserialized));
		}

		$result->addSuccesses(1);
		return $result;
	}
}
