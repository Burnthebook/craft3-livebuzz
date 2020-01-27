<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\models;

use craft\base\Model;

/**
 * @author    Jake Noble
 * @package   Livebuzz
 * @since     0.0.2
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $jsonUrl = '';
    public $authBearer = '';
    public $autoSync = 0;
    public $syncDelay = 900; // 15 minute delay between sync jobs, in seconds

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			['jsonUrl', 'string'],
            ['authBearer', 'string'],
            ['autoSync', 'integer'],
            ['syncDelay', 'integer']
        ];
    }
}
