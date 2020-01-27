<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\jobs;

use Craft;
use Throwable;
use craft\queue\BaseJob;
use burnthebook\livebuzz\Livebuzz;

/**
 * @author    Jake Noble
 * @package   Livebuzz
 * @since     0.0.2
 */
class SyncJob extends BaseJob
{
    // Public Methods
    // =========================================================================

    public $manual = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function execute($queue)
    {
        $job = $this;

        $setProgressFunction = function ($progress, $label = null) use ($job, $queue) {
            $job->setProgress($queue, $progress, $label);
        };

		Livebuzz::getInstance()->syncService->startJsonFeedSync($setProgressFunction);
    }

    public static function getDefaultDescription(): string
    {
        return Craft::t('livebuzz', 'Livebuzz Sync');
    }

    public static function getDefaultManualDescription(): string
    {
        return Craft::t('livebuzz', 'Livebuzz Sync (Manual)');
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return $this->manual ? self::getDefaultManualDescription() : self::getDefaultDescription();
    }
}
