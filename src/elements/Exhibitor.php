<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\elements;

use craft\base\Element;
use Craft;
use craft\elements\db\ElementQueryInterface;
use burnthebook\livebuzz\elements\db\ExhibitorQuery;

/**
 * @author    Jake Noble
 * @package   Livebuzz
 * @since     0.0.1
 */
class Exhibitor extends Element
{
    const TABLE     = '{{%livebuzz_exhibitors}}';
    const TABLE_STD = 'livebuzz_exhibitors';

    // Public Properties
    // =========================================================================

    public $logo;
    public $companyName;
    public $description;
    public $addresses = [];
    public $telephone;
    public $emailAddress;
    public $websiteUrl;
    public $stands = [];
    public $socialMediaChannels = [];


    // Private Properties
    // =========================================================================

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('livebuzz', 'Exhibitor');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('livebuzz', 'Exhibitors');
    }

    /**
     * @inheritdoc
     * @return ExhibitorQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new ExhibitorQuery(static::class);
    }

    protected static function defineSortOptions(): array
    {
        return [
            'companyName' => \Craft::t('livebuzz', 'Company Name'),
            'description' => \Craft::t('livebuzz', 'Description'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
			'companyName' => \Craft::t('livebuzz', 'Company Name'),
			'description' => \Craft::t('livebuzz', 'Description'),
        ];
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['exhibitorRef', 'companyName'];
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('livebuzz', 'All Exhibitors'),
                'criteria' => []
            ],
        ];
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['companyName'], 'string'],
            [['companyName', 'description'], 'required'],
        ];
    }

    public function __toString()
    {
        if ($this->companyName) {
            return $this->companyName;
        }

        return parent::__toString();
    }

    // Exhibitors
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        $data = [
            'logo' => $this->logo,
            'companyName' => $this->companyName,
            'description' => $this->description,
            'telephone' => $this->telephone,
            'emailAddress' => $this->emailAddress,
            'websiteUrl' => $this->websiteUrl,
            'addressesJson' => \GuzzleHttp\json_encode($this->addresses),
            'standsJson' => \GuzzleHttp\json_encode($this->stands),
            'socialMediaChannelsJson' => \GuzzleHttp\json_encode($this->socialMediaChannels),
        ];

        if ($isNew) {
            $data['id'] = $this->id;
            \Craft::$app->db->createCommand()
                ->insert(self::TABLE, $data)
                ->execute();
        } else {
            \Craft::$app->db->createCommand()
                ->update(self::TABLE, $data, ['id' => $this->id])
                ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
    }

    /**
     * @inheritdoc
     */
    public function beforeMoveInStructure(int $structureId): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterMoveInStructure(int $structureId)
    {
    }
}
