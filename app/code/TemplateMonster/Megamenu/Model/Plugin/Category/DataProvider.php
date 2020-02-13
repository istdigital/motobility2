<?php
namespace TemplateMonster\Megamenu\Model\Plugin\Category;

class DataProvider
{
    protected $_storeManager;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;

    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject,$result)
    {
        $categoryId = $subject->getCurrentCategory()->getId();
        if ($categoryId && isset($result[$categoryId]['mm_image'])) {
            unset($result[$categoryId]['mm_image']);
            $category = $subject->getCurrentCategory();
            $result[$categoryId]['mm_image'][0]['name'] = $category->getData('mm_image');
            $result[$categoryId]['mm_image'][0]['url'] = $this->getImageUrl($category->getData('mm_image'));

        }
        return $result;
    }

    /**
     * @param $image
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getImageUrl($image)
    {
        $url = false;
        if ($image) {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }
}