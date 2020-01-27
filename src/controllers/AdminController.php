<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use burnthebook\livebuzz\Livebuzz;

/**
 * @author    Jake Noble
 * @package   Livebuzz
 * @since     0.0.2
 */
class AdminController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(UrlHelper::cpUrl('livebuzz/exhibitors'));
    }

    /**
     * @return mixed
     */
    public function actionSyncNow()
    {
        $syncService = Livebuzz::getInstance()->syncService;

        $syncService->removeQueuedSyncJobs(true);
        $syncService->queueSyncJob(0, true);

        Craft::$app->session->setNotice(Craft::t('livebuzz', 'Sync has been started'));

        return $this->redirect(UrlHelper::cpUrl('livebuzz'));
    }
}
