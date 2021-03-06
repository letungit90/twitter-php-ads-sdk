<?php

namespace Hborras\TwitterAdsSDK\TwitterAds\TailoredAudience;

use Hborras\TwitterAdsSDK\TONUpload;
use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Hborras\TwitterAdsSDK\TwitterAds\Resource;
use Hborras\TwitterAdsSDK\TwitterAds\TailoredAudience\Exception\InvalidType;

final class TailoredAudience extends Resource
{
    const RESOURCE_COLLECTION = 'accounts/{account_id}/tailored_audiences';
    const RESOURCE            = 'accounts/{account_id}/tailored_audiences/{id}';

    const LIST_TYPE_EMAIL        = 'EMAIL';
    const LIST_TYPE_DEVICE_ID    = 'DEVICE_ID';
    const LIST_TYPE_TWITTER_ID   = 'TWITTER_ID';
    const LIST_TYPE_HANDLE       = 'HANDLE';
    const LIST_TYPE_PHONE_NUMBER = 'PHONE_NUMBER';

    /** Writable */
    protected $list_type;
    protected $name;

    protected $properties = [
        'name',
        'list_type',
    ];

    /** Read Only */
    protected $deleted;
    protected $targetable;
    protected $audience_size;
    protected $id;
    protected $updated_at;
    protected $created_at;
    protected $audience_type;
    protected $reasons_not_targetable;
    protected $targetable_types;
    protected $partner_source;
    protected $metadata;

    /**
     * TailoredAudience constructor.
     * @param Account|null $account
     * @param null $id
     */
    public function __construct(Account $account = null, $id = null)
    {
        parent::__construct($account);
        $this->id = $id;
    }

    /**
     * Uploads and creates a new tailored audience
     *
     * @param $filePath
     * @param $name
     * @param $listType
     * @return Resource
     * @throws TwitterAds\Errors\ServerError
     */
    public function create($filePath, $name, $listType)
    {
        $upload = new TONUpload($this->getAccount()->getTwitterAds(), $filePath);

        $this->createAudience($name, $listType);
        $location = $upload->perform();
        $tailoredAudienceChange = new TailoredAudienceChanges($this->getAccount());
        $tailoredAudienceChange->updateAudience($this->getId(), $location, $listType, TailoredAudienceChanges::ADD);

        return $this->reload();
    }

    /**
     * Create a simple tailored audience object
     *
     * @param $name
     * @param $listType
     * @return $this
     */
    public function createAudience($name, $listType)
    {
        $params = ['name' => $name, 'list_type' => $listType];
        $resource = str_replace(static::RESOURCE_REPLACE, $this->getAccount()->getId(), static::RESOURCE_COLLECTION);
        $response = $this->getAccount()->getTwitterAds()->post($resource, $params);

        return $this->fromResponse($response->getBody()->data);
    }

    /**
     * Returns the TailoredAudienceChange with the status
     * @return null
     */
    public function status()
    {
        $tailoredAudienceChange = new TailoredAudienceChanges($this->getAccount());
        return $tailoredAudienceChange->status($this->getId());
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return filter_var($this->deleted, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return boolean
     */
    public function isTargetable()
    {
        return filter_var($this->targetable, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return integer
     */
    public function getAudienceSize()
    {
        return intval($this->audience_size);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return mixed
     */
    public function getListType()
    {
        return $this->assureValidType($this->list_type);
    }

    /**
     * @param string $type
     */
    public function setListType($type)
    {
        $this->list_type = $this->assureValidType($type);
    }

    /**
     * @return string
     */
    public function getAudienceType()
    {
        return $this->audience_type;
    }

    /**
     * @return array
     */
    public function getReasonsNotTargetable()
    {
        return $this->reasons_not_targetable;
    }

    /**
     * @return array
     */
    public function getTargetableTypes()
    {
        return $this->targetable_types;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPartnerSource()
    {
        return $this->partner_source;
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::LIST_TYPE_DEVICE_ID,
            self::LIST_TYPE_EMAIL,
            self::LIST_TYPE_HANDLE,
            self::LIST_TYPE_PHONE_NUMBER,
            self::LIST_TYPE_TWITTER_ID,
        ];
    }

    /**
     * Asserts that the given type is valid
     *
     * @param string $type
     * @throws InvalidType - if type is invalid or null
     *
     * @return string
     */
    private function assureValidType($type)
    {
        foreach (self::getTypes() as $allowedType) {
            if ($type === $allowedType) {
                return $type;
            }
        }

        throw new InvalidType(
            sprintf('"%s" is not a valid type for %s', $type, TailoredAudience::class)
        );
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
