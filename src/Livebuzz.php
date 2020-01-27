<?php

namespace burnthebook\livebuzz;

use Craft;
use craft\base\Plugin;
use burnthebook\livebuzz\models\Settings;
use burnthebook\livebuzz\elements\Exhibitor as ExhibitorElement;
use burnthebook\livebuzz\services\SyncService;
use burnthebook\livebuzz\services\TwigService;
use burnthebook\livebuzz\jobs\SyncJob;
use craft\console\Application as ConsoleApplication;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\queue\Queue;
use craft\services\Elements;
use craft\services\Plugins;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Event;
use yii\queue\ExecEvent;

/**
 * Class Livebuzz
 * @package burnthebook\livebuzz
 *
 * @property  SyncService $syncService
 * @property  TwigService $twigService
 */
class Livebuzz extends Plugin
{

	// Static Properties
	// =========================================================================

	/**
	 * @var Livebuzz
	 */
	public static $plugin;

	// Public Properties
	// =========================================================================

	/**
	 * @var string
	 */
	public $schemaVersion = '0.0.2';

	// Public Methods
	// =========================================================================

	/**
	 * @return Settings
	 */
	public function getSettings()
	{
		return parent::getSettings();
	}

	public function init()
	{
		parent::init();
		self::$plugin = $this;

		// Add in our console commands
		if (Craft::$app instanceof ConsoleApplication) {
			$this->controllerNamespace = 'burnthebook\livebuzz\console\controllers';
		}

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_SITE_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules['siteActionTrigger1'] = 'livebuzz/default';
			}
		);

		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function (RegisterUrlRulesEvent $event) {
				$event->rules['livebuzz'] = 'livebuzz/admin/index';
				$event->rules['livebuzz/sync-now'] = 'livebuzz/admin/sync-now';
			}
		);

		Event::on(
			Elements::class,
			Elements::EVENT_REGISTER_ELEMENT_TYPES,
			function (RegisterComponentTypesEvent $event) {
				$event->types[] = ExhibitorElement::class;
			}
		);

		// queue subsequent sync jobs
		Craft::$app->queue->on(Queue::EVENT_AFTER_EXEC, function ($event) {
			/** @var ExecEvent $event */
			$settings = Livebuzz::getInstance()->getSettings();
			if ($event->job instanceof SyncJob && $settings->autoSync) {
				$this->syncService->queueSyncJob($settings->syncDelay);
			}
		});

		if ($this->getSettings()->autoSync) {
			// ensure there's a sync job in the queue e.g. if autoSync was enabled post-install
			if (empty($this->syncService->getQueuedSyncJobs(true))) {
				$this->syncService->queueSyncJob($this->getSettings()->syncDelay);
			}
		} else {
			$this->syncService->removeQueuedSyncJobs();
		}

		Event::on(
			Plugins::class,
			Plugins::EVENT_AFTER_UNINSTALL_PLUGIN,
			function (PluginEvent $event) {
				if ($event->plugin !== $this) {
					return;
				}
				$this->syncService->removeQueuedSyncJobs(true);
			}
		);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function (Event $e) {
				/** @var CraftVariable $variable */
				$variable = $e->sender;

				$variable->set('livebuzz', \burnthebook\livebuzz\services\TwigService::class);
			}
		);

		Craft::info(
			Craft::t(
				'livebuzz',
				'{name} plugin loaded',
				['name' => $this->name]
			),
			__METHOD__
		);
	}

	public function getCpNavItem()
	{
		$item = parent::getCpNavItem();
		$item['subnav'] = [
			'exhibitors' => ['label' => Craft::t('livebuzz', 'Exhibitor'), 'url' => 'livebuzz/exhibitors'],
		];
		return $item;
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	protected function createSettingsModel()
	{
		return new \burnthebook\livebuzz\models\Settings();
	}

	/**
	 * @return string
	 * @throws LoaderError
	 * @throws RuntimeError
	 * @throws SyntaxError
	 */
	protected function settingsHtml(): string
	{
		return Craft::$app->view->renderTemplate(
			'livebuzz/settings',
			[
				'settings' => $this->getSettings()
			]
		);
	}
}