<?php

namespace Zendesk\Zendesk\Plugin\Config\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Group as OriginalGroup;
use Zendesk\API\Exceptions\AuthException;

class Group
{
    const GROUP_ID = 'brand_mapping';
    /**
     * @var \Zendesk\Zendesk\Helper\Api
     */
    protected $apiHelper;
    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface[]|null
     */
    protected $stores;

    /**
     * Group constructor.
     * @param \Zendesk\Zendesk\Helper\Api $apiHelper
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        \Zendesk\Zendesk\Helper\Api $apiHelper,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->apiHelper = $apiHelper;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Get store views as options array
     *
     * @return array
     */
    protected function getStoreOptions()
    {
        if ($this->stores === null) {
            $stores = $this->storeRepository->getList();

            $this->stores = [];

            foreach ($stores as $store) {
                if ($store->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
                    continue;
                }

                $this->stores[] = [
                    'value' => $store->getId(),
                    'label' => $store->getName()
                ];
            }
        }

        return $this->stores;
    }

    /**
     * Get brand dynamic config fields array
     *
     * @return array
     * @throws AuthException
     */
    protected function getDynamicFields()
    {
        try {
            $this->apiHelper->tryAuthenticate();
        } catch (AuthException $e) {
            // not configured -- nothing to do.
            return [];
        }

        $api = $this->apiHelper->getZendeskApiInstance();

        $brands = $api->brands()->getBrands();

        $storeOptions = $this->getStoreOptions();

        if (count($brands->brands) < 2 || count($storeOptions) < 2) {
            return []; // No need for this UI if there is only one store or only one brand
        }

        $dynamicConfigFields = [];

        foreach ($brands->brands as $index => $brand) {
            $configId = \Zendesk\Zendesk\Helper\Config::BRAND_FIELD_CONFIG_PATH_PREFIX . $brand->id;

            $dynamicConfigFields[$configId] = [
                'id' => $configId,
                'type' => 'multiselect',
                'sortOrder' => ($index * 10), // Generate unique and deterministic sortOrder values
                'showInDefault' => '1',       // In this case, only show fields at default scope
                'showInWebsite' => '0',
                'showInStore' => '0',
                'label' => $brand->name,
                'options' => [                // Since this is a multiselect, generate options dynamically.
                    'option' => $storeOptions
                ],
                'comment' => __(
                    'Select store(s) to map to brand <strong>%1</strong>.',
                    $brand->name
                ),
                '_elementType' => 'field',
                'path' => \Zendesk\Zendesk\Helper\Config::BRAND_FIELD_GROUP_PREFIX
            ];
        }

        return $dynamicConfigFields;
    }

    /**
     * Add brand mapping config fields
     *
     * @param OriginalGroup $subject
     * @param callable $proceed
     * @param array $data
     * @param $scope
     * @return mixed
     * @throws AuthException
     */
    public function aroundSetData(OriginalGroup $subject, callable $proceed, array $data, $scope)
    {
        // This method runs for every group.
        // Add a condition to check for the one to which we're
        // interested in adding fields.
        if ($data['id'] == self::GROUP_ID) {
            $dynamicFields = $this->getDynamicFields();

            if (!empty($dynamicFields)) {
                $children = isset($data['children']) ? $data['children'] : [];

                $children += $dynamicFields;

                $data['children'] = $children;
            }
        }

        return $proceed($data, $scope);
    }
}
