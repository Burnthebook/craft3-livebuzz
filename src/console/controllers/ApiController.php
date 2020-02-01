<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\console\controllers;

use Throwable;
use yii\console\Controller;
use yii\console\ExitCode;
use burnthebook\livebuzz\Livebuzz;
use \GuzzleHttp\Exception\GuzzleException;

class ApiController extends Controller
{
	/**
	 * @return int
	 * @throws Throwable
	 * @throws GuzzleException
	 */
    public function actionSync()
    {
        Livebuzz::getInstance()->syncService->startJsonFeedSync();

        return ExitCode::OK;
    }
}
