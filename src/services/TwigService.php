<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\services;

use Craft;
use craft\base\Component;
use burnthebook\livebuzz\elements\db\ExhibitorQuery;
use burnthebook\livebuzz\elements\Exhibitor;

class TwigService extends Component
{
	/**
	 * @param null $criteria
	 * @return ExhibitorQuery
	 */
	public function exhibitors($criteria = null): ExhibitorQuery
	{
		$query = Exhibitor::find();
		if ($criteria) {
			Craft::configure($query, $criteria);
		}
		return $query;
	}

	public function exhibitorByIdentifier($identifier) {
		return Exhibitor::findOne(['identifier' => $identifier]);
	}
}
