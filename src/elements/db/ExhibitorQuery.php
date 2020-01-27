<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use burnthebook\livebuzz\elements\Exhibitor;

class ExhibitorQuery extends ElementQuery
{
	public $companyName;

	public function name($value)
	{
		$this->companyName = $value;

		return $this;
	}

	protected function beforePrepare(): bool
	{
		$this->joinElementTable(Exhibitor::TABLE_STD);

		$this->query->select([
			Exhibitor::TABLE_STD . '.logo',
			Exhibitor::TABLE_STD . '.companyName',
			Exhibitor::TABLE_STD . '.description',
			Exhibitor::TABLE_STD . '.telephone',
			Exhibitor::TABLE_STD . '.emailAddress',
			Exhibitor::TABLE_STD . '.websiteUrl',
			Exhibitor::TABLE_STD . '.addressesJson',
			Exhibitor::TABLE_STD . '.standsJson',
			Exhibitor::TABLE_STD . '.socialMediaChannelsJson',
		]);

		if ($this->companyName) {
			$this->subQuery->andWhere(Db::parseParam(Exhibitor::TABLE_STD . '.companyName', $this->companyName));
		}

		return parent::beforePrepare();
	}
}
