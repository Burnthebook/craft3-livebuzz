<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\services;

use burnthebook\livebuzz\sync\JsonFeedSync;
use Craft;
use Throwable;
use craft\base\Component;
use yii\db\Query;
use burnthebook\livebuzz\jobs\SyncJob;
use \GuzzleHttp\Exception\GuzzleException;

/**
 * @author    Jake Noble
 * @package   Livebuzz
 * @since     0.0.2
 */
class SyncService extends Component
{
	const TABLE_QUEUE = '{{%queue}}';

    // Public Methods
    // =========================================================================

	/**
	 * @param callable|null $setProgressFunction A function that sets the progress of the sync.
	 * Accepts a number between 0 and 1 as the first parameter, and an optional label as the second
	 * @throws Throwable
	 * @throws GuzzleException
	 */
    public function startJsonFeedSync(callable $setProgressFunction = null)
    {
        (new JsonFeedSync($setProgressFunction))->start();
    }

    /**
     * @param bool $includeManualSyncJobs
     * @return array
     */
    public function getQueuedSyncJobs($includeManualSyncJobs = false)
    {
        $query = (new Query())
            ->from(self::TABLE_QUEUE)
            ->where(['description' => SyncJob::getDefaultDescription()]);

        if ($includeManualSyncJobs) {
            $query->orWhere(['description' => SyncJob::getDefaultManualDescription()]);
        }

        return $query->all();
    }

    /**
     * @param bool $includingManualSyncJobs
     */
    public function removeQueuedSyncJobs($includingManualSyncJobs = false)
    {
        foreach ($this->getQueuedSyncJobs($includingManualSyncJobs) as $job) {
            Craft::$app->queue->release($job['id']);
        }
    }

    /**
     * @param int $delay in seconds
     * @param bool $manual If the sync is triggered manually by a user
     */
    public function queueSyncJob($delay = 0, $manual = false)
    {
        $job = new SyncJob();
        $job->manual = $manual;

        Craft::$app->queue->delay($delay)->push($job);
    }
}
