<?php
/**
 * Livebuzz plugin for Craft CMS 3.x
 *
 * @link      https://github.com/Burnthebook
 * @copyright Copyright (c) 2020 Burnthebook Ltd.
 */

namespace burnthebook\livebuzz\elements;

use Craft;
use craft\elements\db\ElementQueryInterface;
use burnthebook\livebuzz\elements\db\ExhibitorQuery;

/**
 * @author    Jake Noble
 * @package   Livebuzz
 * @since     0.0.1
 */
class Exhibitor extends AbstractComparableElement
{
	const TABLE = '{{%livebuzz_exhibitors}}';
	const TABLE_STD = 'livebuzz_exhibitors';

	// Public Properties
	// =========================================================================

	public $logo;
	public $identifier;
	public $companyName;
	public $description;
	public $telephone;
	public $emailAddress;
	public $websiteUrl;
	public $addresses = [];
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

	protected static function defineComparableAttributes(): array
	{
		return [
			'logo',
			'identifier',
			'companyName',
			'description',
			'addresses',
			'telephone',
			'emailAddress',
			'websiteUrl',
			'stands',
			'socialMediaChannels'
		];
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
			'companyName' => \Craft::t('livebuzz', 'Company Name')
		];
	}

	protected static function defineTableAttributes(): array
	{
		return [
			'companyName' => \Craft::t('livebuzz', 'Company Name')
		];
	}

	protected static function defineSearchableAttributes(): array
	{
		return ['companyName'];
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
			[['companyName', 'identifier'], 'required'],
		];
	}

	public function __toString()
	{
		if ($this->companyName) {
			return $this->companyName;
		}

		return parent::__toString();
	}

	/**
	 * @param string|null $value JSON encoded array of stand positions
	 */
	public function setStandsJson($value)
	{
		$this->stands = (array)\GuzzleHttp\json_decode($value, true);
	}

	/**
	 * @param string|null $value JSON encoded array of social media links
	 */
	public function setSocialMediaChannelsJson($value)
	{
		$this->socialMediaChannels = (array)\GuzzleHttp\json_decode($value, true);
	}

	/**
	 * @param string|null $value JSON encoded array of addresses
	 */
	public function setAddressesJson($value)
	{
		$this->addresses = (array)\GuzzleHttp\json_decode($value, true);
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
			'identifier' => $this->identifier,
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
