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
use DateTime;
use DateTimeZone;
use Throwable;
use burnthebook\livebuzz\elements\Exhibitor;
use burnthebook\livebuzz\Livebuzz;
use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

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
    private $exhibitorRefs = [];

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
     */
    public function start()
    {
        echo "Starting sync from JSON ... \n";

        if (empty($this->feedUrl)) {
            echo "No JSON URL specified. Exiting. \n";
            return;
        }

		$client = new Client();

		$request = new Request(
			"GET",
			$this->feedUrl ."rest/v3/campaign/geo-connect-asia-2020/exhibitors/shanghai-merrypal-import-export-co-ltd?expand%5B0%5D=addresses&expand%5B0%5D=phones",
			[
				"Authorization" => $this->bearer
			],
			"");

		$response = $client->send($request);
		echo "Response HTTP : " . $response->getStatusCode() . "";
		$body = $response->getBody();
		var_dump(json_decode($body, true));
		die;

        $json = simplexml_load_file($this->feedUrl);

        $this->entityCount = $this->getEntityCount($json);

        $this->syncExhibitors($json);

        $this->processDeletions();

        echo "Sync finished. \n";
    }

    // Private Methods
    // =========================================================================

    /**
     * Gets the total number of Venues + Shows + Exhibitors that are present in the feed
     * @param JsonFeedSync $json
     * @return int
     */
    private function getEntityCount(JsonFeedSync $json)
    {
        $count = $json->count();

        foreach ($json->venue as $venue) {
            $count += count($venue->shows->show);
            foreach ($venue->shows->show as $show) {
                $count += count($show->exhibitors->exhibitor);
            }
        }

        return $count;
    }

    /**
     * @param JsonFeedSync $json
     * @throws Throwable
     */
    private function syncExhibitors(JsonFeedSync $json)
    {
//        foreach ($json->exhibitors->exhibitor as $i => $xmlExhibitor) {
//            $exhibitor = $this->getExhibitorFromXML($xmlExhibitor, $show);
//
//            if (!$exhibitor->exhibitorRef) {
//                echo "Missing exhibitorRef at iteration $i \n";
//                continue;
//            }
//
//            /** @var Exhibitor $oldExhibitor */
//            $oldExhibitor = Exhibitor::find()->exhibitorRef($exhibitor->exhibitorRef)->one();
//
//            if ($oldExhibitor) {
//                if ($exhibitor->isDifferent($oldExhibitor)) {
//                    echo "Updating Exhibitor Ref $exhibitor->exhibitorRef ... \n";
//                    $exhibitor->syncToElement($oldExhibitor);
//                    $exhibitor = $oldExhibitor;
//                    Craft::$app->elements->saveElement($oldExhibitor, false);
//                } else {
//                    echo "Skipping Exhibitor Ref $exhibitor->exhibitorRef - no change detected ... \n";
//                    $exhibitor = $oldExhibitor;
//                }
//            } else {
//                echo "Creating Exhibitor Ref $exhibitor->exhibitorRef ... \n";
//                Craft::$app->elements->saveElement($exhibitor, false);
//            }
//
//            // store exhibitor ref so we can sort out deletions later on
//            $this->exhibitorRefs[] = $exhibitor->exhibitorRef;
//
//            $this->updateSyncProgress();
//        }
    }

    /**
     * Deletes elements that were not present in the JSON feed
     * @throws Throwable
     */
    private function processDeletions()
    {
        $deleteExhibitors = Exhibitor::find()->excludeExhibitorRefs($this->exhibitorRefs)->all();
        /** @var Exhibitor $deleteExhibitor */
        foreach ($deleteExhibitors as $deleteExhibitor) {
            echo "Deleting Exhibitor Ref $deleteExhibitor->exhibitorRef ... \n";
            Craft::$app->elements->deleteElement($deleteExhibitor, true);
        }
    }

    /**
     * @param JsonFeedSync $json
     * @return Exhibitor
     * @throws Throwable
     */
    private function getExhibitorFromJson(JsonFeedSync $json): Exhibitor
    {
        $exhibitor = new Exhibitor();
//        $exhibitor->showId = $show->id;
//        $exhibitor->exhibitorRef = isset($xml['id']) ? (integer)$xml['id'] : null;
//        $exhibitor->name = trim($xml->name);
//        $exhibitor->dateTime = $this->getDateTimeFromXML($xml2, 'date_time');
//        $exhibitor->openingTime = $this->getDateTimeFromXML($xml2, 'opening_time');
//        $exhibitor->onSaleTime = $this->getDateTimeFromXML($xml2, 'onsale_time');
//        $exhibitor->duration = (integer)trim($xml2->duration);
//        $exhibitor->available = (integer)trim($xml2->available);
//        $exhibitor->capacity = (integer)trim($xml2->capacity);
//        $exhibitor->venueLayout = trim($xml->venue_layout);
//        $exhibitor->comment = trim($xml2->comment);
//        $exhibitor->url = trim($xml2->url);
//        $exhibitor->status = trim($xml2->status);
//        $exhibitor->fee = (float)trim($xml2->transaction->fee);
//        $exhibitor->feeCurrency = trim($xml2->transaction->fee->attributes()['currency']);
//        $exhibitor->maximumTickets = (integer)trim($xml2->transaction->maximum_tickets);
//        $exhibitor->prices = [];

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
