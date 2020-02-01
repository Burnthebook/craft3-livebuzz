<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\migrations;

use craft\db\Migration;
use burnthebook\livebuzz\elements\Exhibitor;

class Install extends Migration
{
	const TABLE_ELEMENTS = '{{%elements}}';

	public function safeUp()
	{
		$this->createEventsTable();
	}

	public function safeDown()
	{
		// clean-up elements table
		$this->delete(self::TABLE_ELEMENTS, ['in', 'type', [
			Exhibitor::class
		]]);

		// drop plugin's tables
		$this->dropTableIfExists(Exhibitor::TABLE);
	}

	// Private Methods
	// =========================================================================

	private function createEventsTable()
	{
		if ($this->db->tableExists(Exhibitor::TABLE)) {
			return;
		}

		$this->createTable(Exhibitor::TABLE, [
			'id' => $this->integer()->notNull(),
			'identifier' => $this->char(255)->notNull(),
			'companyName' => $this->char(255)->notNull(),
			'logo' => $this->char(255),
			'telephone' => $this->char(255),
			'emailAddress' => $this->char(255),
			'websiteUrl' => $this->text(),
			'addressesJson' => $this->text(),
			'standsJson' => $this->text(),
			'socialMediaChannelsJson' => $this->text(),
			'description' => $this->text(),
			'dateCreated' => $this->dateTime()->notNull(),
			'dateUpdated' => $this->dateTime()->notNull(),
			'uid' => $this->uid(),
			'PRIMARY KEY(id)',
		]);

		// Create indices
		$this
			->createIndex(
				$this->db->getIndexName(Exhibitor::TABLE, 'identifier'),
				Exhibitor::TABLE,
				'identifier');
		$this
			->createIndex(
				$this->db->getIndexName(Exhibitor::TABLE, 'companyName'),
				Exhibitor::TABLE,
				'companyName');

		$this->createIndex(
			$this->db->getIndexName(Exhibitor::TABLE, 'description'),
			Exhibitor::TABLE,
			'description'
		);
	}
}
