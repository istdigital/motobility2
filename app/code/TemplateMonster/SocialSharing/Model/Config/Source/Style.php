<?php

namespace TemplateMonster\SocialSharing\Model\Config\Source;

use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Style frontend model.
 */
class Style implements ArrayInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var AssetRepository
     */
    protected $_assetRepo;

    /**
     * Style constructor.
     *
     * @param RequestInterface $request
     * @param AssetRepository  $assetRepo
     */
    public function __construct(
        RequestInterface $request,
        AssetRepository $assetRepo
    ) {
        $this->_request = $request;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach (range(1, 8) as $i) {
            $options[] = [
                'value' => "style{$i}",
                'label' => sprintf(
                    '<img src="%s" alt="" />',
                    $this->getImageUrl("style{$i}.png")
                ),
            ];
        }
        $options[] = [
            'value' => 'custom_button',
            'label' => __('Custom Button'),
        ];
        $options[] = [
            'value' => 'custom_code',
            'label' => __('Custom Code'),
        ];

        return $options;
    }

    /**
     * Get image url.
     *
     * @param string $fileId
     *
     * @return string
     */
    public function getImageUrl($fileId)
    {
        return $this->_assetRepo->getUrlWithParams("TemplateMonster_SocialSharing::images/{$fileId}", [
            '_secure' => $this->_request->isSecure(),
        ]);
    }
}
