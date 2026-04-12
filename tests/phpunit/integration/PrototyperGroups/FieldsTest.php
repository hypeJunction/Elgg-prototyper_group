<?php

namespace PrototyperGroups;

use Elgg\IntegrationTestCase;
use hypeJunction\Prototyper\Groups\ContentAccessModeField;
use hypeJunction\Prototyper\Groups\MembershipField;
use hypeJunction\Prototyper\Groups\NameField;
use hypeJunction\Prototyper\Groups\OwnerField;
use hypeJunction\Prototyper\Groups\ToolsField;
use hypeJunction\Prototyper\Groups\VisibilityField;

/**
 * Tests for field handler classes.
 *
 * These tests require the hypePrototyper plugin to be active because
 * the field classes extend AttributeField / MetadataField from that
 * package. Tests are skipped when hypePrototyper is not available.
 */
class FieldsTest extends IntegrationTestCase {

	public function up() {
		if (!class_exists('hypeJunction\\Prototyper\\Elements\\AttributeField')) {
			$this->markTestSkipped('hypePrototyper parent classes not available.');
		}
	}

	public function down() {
	}

	public function getPluginID(): string {
		return 'prototyper_group';
	}

	public function testNameFieldExtendsAttributeField(): void {
		$this->assertTrue(is_subclass_of(
			NameField::class,
			'hypeJunction\\Prototyper\\Elements\\AttributeField'
		));
	}

	public function testMembershipFieldExtendsMetadataField(): void {
		$this->assertTrue(is_subclass_of(
			MembershipField::class,
			'hypeJunction\\Prototyper\\Elements\\MetadataField'
		));
	}

	public function testVisibilityFieldExtendsMetadataField(): void {
		$this->assertTrue(is_subclass_of(
			VisibilityField::class,
			'hypeJunction\\Prototyper\\Elements\\MetadataField'
		));
	}

	public function testContentAccessModeFieldExtendsMetadataField(): void {
		$this->assertTrue(is_subclass_of(
			ContentAccessModeField::class,
			'hypeJunction\\Prototyper\\Elements\\MetadataField'
		));
	}

	public function testOwnerFieldExtendsAttributeField(): void {
		$this->assertTrue(is_subclass_of(
			OwnerField::class,
			'hypeJunction\\Prototyper\\Elements\\AttributeField'
		));
	}

	public function testToolsFieldExtendsMetadataField(): void {
		$this->assertTrue(is_subclass_of(
			ToolsField::class,
			'hypeJunction\\Prototyper\\Elements\\MetadataField'
		));
	}

	public function testNameFieldStripsTagsAndAssignsName(): void {
		$group = new \ElggGroup();
		$field = $this->getMockBuilder(NameField::class)
			->disableOriginalConstructor()
			->onlyMethods(['getShortname'])
			->getMock();
		$field->method('getShortname')->willReturn('name');

		set_input('name', '<b>Malicious</b> Name');
		$result = $field->handle($group);
		set_input('name', null);

		$this->assertSame($group, $result);
		$this->assertSame('Malicious Name', $group->name);
	}

	public function testMembershipFieldHandleSetsPublicMembership(): void {
		$group = new \ElggGroup();
		$field = $this->getMockBuilder(MembershipField::class)
			->disableOriginalConstructor()
			->onlyMethods(['getShortname'])
			->getMock();
		$field->method('getShortname')->willReturn('membership');

		set_input('membership', ACCESS_PUBLIC);
		$field->handle($group);
		set_input('membership', null);

		$this->assertEquals(ACCESS_PUBLIC, $group->membership);
	}

	public function testMembershipFieldHandleSetsPrivateForNonPublic(): void {
		$group = new \ElggGroup();
		$field = $this->getMockBuilder(MembershipField::class)
			->disableOriginalConstructor()
			->onlyMethods(['getShortname'])
			->getMock();
		$field->method('getShortname')->willReturn('membership');

		set_input('membership', ACCESS_LOGGED_IN);
		$field->handle($group);
		set_input('membership', null);

		$this->assertEquals(ACCESS_PRIVATE, $group->membership);
	}

	public function testContentAccessModeFieldHandleSetsMode(): void {
		$group = $this->createGroup();
		$field = $this->getMockBuilder(ContentAccessModeField::class)
			->disableOriginalConstructor()
			->onlyMethods(['getShortname'])
			->getMock();
		$field->method('getShortname')->willReturn('content_access_mode');

		set_input('content_access_mode', \ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY);
		$field->handle($group);
		set_input('content_access_mode', null);

		$this->assertEquals(
			\ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY,
			$group->getContentAccessMode()
		);
	}

	public function testOwnerFieldHandleNoOpForUnsavedEntity(): void {
		$group = new \ElggGroup();
		$field = $this->getMockBuilder(OwnerField::class)
			->disableOriginalConstructor()
			->onlyMethods(['getShortname'])
			->getMock();
		$field->method('getShortname')->willReturn('owner_guid');

		set_input('owner_guid', 12345);
		$result = $field->handle($group);
		set_input('owner_guid', null);

		$this->assertSame($group, $result);
		// Entity has no guid, so owner should remain unchanged
		$this->assertEmpty($group->owner_guid);
	}
}
