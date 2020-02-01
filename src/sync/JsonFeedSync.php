<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * Pulls Exhibitors from the the Livebuzz API to a website, it also updates and removes Exhibitors.
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\sync;

use Craft;
use Throwable;
use burnthebook\livebuzz\elements\Exhibitor;
use burnthebook\livebuzz\Livebuzz;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Exception\GuzzleException;

class JsonFeedSync
{
	// Public Properties
	// =========================================================================

	/** @var string */
	public $feedUrl;

	/** @var string */
	public $bearer;

	/** @var callable */
	public $setProgressFunction;

	// Private Properties
	// =========================================================================

	private $entityCount = 0;
	private $entityCounter = 0;
	private $identifiers = [];

	// Public Methods
	// =========================================================================

	/**
	 * @param callable|null $setProgressFunction A function that sets the progress of the sync.
	 * Accepts a number between 0 and 1 as the first parameter, and an optional label as the second
	 */
	public function __construct(callable $setProgressFunction = null)
	{
		$this->setProgressFunction = $setProgressFunction;
		$this->feedUrl = Livebuzz::getInstance()->getSettings()->jsonUrl;
		$this->bearer = Livebuzz::getInstance()->getSettings()->authBearer;
	}

	/**
	 * @throws Throwable
	 * @throws GuzzleException
	 */
	public function start()
	{
		echo "Starting sync from JSON ... \n";

		if (empty($this->feedUrl)) {
			echo "No JSON URL specified. Exiting. \n";
			return;
		}

		if (empty($this->bearer)) {
			echo "No Bearer specified. Exiting. \n";
			return;
		}

		$client = new Client();

		$expandedParams = 'expand%5B%5D=files&expand%5B%5D=addresses&expand%5B%5D=links&expand%5B%5D=phones';

		$exhibitors = [];
		$json['next_page_url'] = $this->feedUrl . 'exhibitors?' . $expandedParams;

		while (filter_var($json['next_page_url'], FILTER_VALIDATE_URL)) {
			echo $json['next_page_url'] . "\n";
			$request = new Request(
				"GET",
				$json['next_page_url'],
				["Authorization" => $this->bearer],
				"");

			$response = $client->send($request);
			if (200 !== $response->getStatusCode()) {
				echo "Sync returned an error. \n";
				return;
			}

			$body = $response->getBody();
			$json = \GuzzleHttp\json_decode($body, true);

			if (filter_var($json['next_page_url'], FILTER_VALIDATE_URL)) {
				$json['next_page_url'] .= '&' . $expandedParams;
			}
			$exhibitors = array_merge_recursive($exhibitors, $json['data']);
		}

		$this->entityCount = count($exhibitors);

		$this->syncExhibitors($exhibitors);

		$this->processDeletions();

		echo "Sync finished. \n";
	}

	// Private Methods
	// =========================================================================

	/**
	 * @param array $exhibitors
	 * @throws Throwable
	 */
	private function syncExhibitors($exhibitors)
	{
		foreach ($exhibitors as $i => $exhibitorData) {
			$exhibitorElement = $this->getExhibitorFromJson($exhibitorData);

			if (!$exhibitorElement->identifier) {
				echo "Missing identifier at iteration $i \n";
				continue;
			}

			/** @var Exhibitor $oldExhibitor */
			$oldExhibitor = Exhibitor::find()->identifier($exhibitorElement->identifier)->one();

			if ($oldExhibitor) {
				if ($exhibitorElement->isDifferent($oldExhibitor)) {
					echo "Updating Exhibitor Identifier $exhibitorElement->identifier ... \n";
					$exhibitorElement->syncToElement($oldExhibitor);
					$exhibitorElement = $oldExhibitor;
					$oldExhibitor = $this->storeExhibitorLogo($oldExhibitor);
					Craft::$app->elements->saveElement($oldExhibitor, false);
				} else {
					echo "Skipping Exhibitor Identifier $exhibitorElement->identifier - no change detected ... \n";
					$exhibitorElement = $oldExhibitor;
				}
			} else {
				echo "Creating Exhibitor Identifier $exhibitorElement->identifier ... \n";
				$exhibitorElement = $this->storeExhibitorLogo($exhibitorElement);
				Craft::$app->elements->saveElement($exhibitorElement, false);
			}
			// store exhibitor Identifier so we can sort out deletions later on
			$this->identifiers[] = $exhibitorElement->identifier;

			$this->updateSyncProgress();
		}
	}

	/**
	 * @param Exhibitor $exhibitorElement
	 * @return Exhibitor
	 */
	private function storeExhibitorLogo(Exhibitor $exhibitorElement)
	{
		if (!filter_var($exhibitorElement->logo, FILTER_VALIDATE_URL)) {
			return $exhibitorElement;
		}

		$parts = explode('.', $exhibitorElement->logo);
		$filename = md5($exhibitorElement->logo) . '.' . end($parts);
		$imagePath = CRAFT_BASE_PATH . '/web/images/uploads/' . $filename;

		if (file_exists($imagePath)) {
			echo "Logo exists - do not store\n";
			return $exhibitorElement;
		}

		echo "Storing a new logo\n";

		$imageUri = '/images/uploads/' . $filename;
		file_put_contents($imagePath, file_get_contents($exhibitorElement->logo));
		$exhibitorElement->logo = $imageUri;

		return $exhibitorElement;
	}

	/**
	 * Deletes elements that were not present in the JSON feed
	 * @throws Throwable
	 */
	private function processDeletions()
	{
		$deleteExhibitors = Exhibitor::find()->excludeIdentifiers($this->identifiers)->all();
		/** @var Exhibitor $deleteExhibitor */
		foreach ($deleteExhibitors as $deleteExhibitor) {
			echo "Deleting Exhibitor Identifier $deleteExhibitor->identifier ... \n";
			if ($deleteExhibitor->logo) {
				if (file_exists(CRAFT_BASE_PATH . '/web/' . $deleteExhibitor->logo)) {
					unlink(CRAFT_BASE_PATH . '/web/' . $deleteExhibitor->logo);
				}
			}
			Craft::$app->elements->deleteElement($deleteExhibitor);
		}
	}

	/**
	 * @param array $exhibitorData
	 * @return Exhibitor
	 * @throws Throwable
	 */
	private function getExhibitorFromJson($exhibitorData): Exhibitor
	{
		$exhibitor = new Exhibitor();
		$exhibitor->identifier = trim($exhibitorData['identifier']);
		$exhibitor->companyName = trim($exhibitorData['name']);

		$exhibitor->description = trim($exhibitorData['biography']);
		$exhibitor->emailAddress = trim(strtolower($exhibitorData['website_email']));
		$exhibitor->websiteUrl = null;

		if (!empty($exhibitorData['phones'])) {
			$exhibitor->telephone = $exhibitorData['phones'][0]['number_international'];
		}

		if (!empty($exhibitorData['addresses'])) {
			$exhibitorData['addresses'] = array_map(function ($n) {
				return [
					'line_1' => $n['line_1'],
					'line_2' => $n['line_2'],
					'line_3' => $n['line_3'],
					'city' => $n['city'],
					'county' => $n['county'],
					'region' => $n['region'],
					'country' => $n['country']
				];
			}, $exhibitorData['addresses']);
			$exhibitor->addresses = $exhibitorData['addresses'];
		}

		if (!empty($exhibitorData['links'])) {
			$exhibitorData['links'] = array_map(function ($n) {
				return [
					'type' => $n['type'],
					'url' => $n['url']
				];
			}, $exhibitorData['links']);
			$exhibitor->socialMediaChannels = $exhibitorData['links'];
		}

		$exhibitor->stands = $exhibitorData['stands'];

		if (!empty($exhibitorData['files'])) {
			foreach ($exhibitorData['files'] as $file) {
				if ('profile_logo' == $file['identifier']) {
					$exhibitor->logo = $file['url'];
				}
			}
		}

		return $exhibitor;
	}

	private function updateSyncProgress()
	{
		$this->entityCounter++;

		if (is_callable($this->setProgressFunction)) {
			($this->setProgressFunction)(
				$this->entityCounter / $this->entityCount,
				"Processed $this->entityCounter out of $this->entityCount."
			);
		}
	}
}
