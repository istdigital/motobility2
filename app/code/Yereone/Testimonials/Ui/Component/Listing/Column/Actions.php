<?php

namespace Yereone\Testimonials\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Actions extends Column
{
    /** Url path */
    const EDIT_URL = "testimonial/testimonial/edit";
    const DELETE_URL = "testimonial/testimonial/delete";

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * StoreActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData("name");
                if ($item["id"]) {
                    $item[$name]["edit"] = [
                        "href" => $this->urlBuilder->getUrl(self::EDIT_URL, array("id" => $item["id"])),
                        "label" => "Edit "
                    ];
                    $item[$name]["delete"] = [
                        "href" => $this->urlBuilder->getUrl(self::DELETE_URL, array("id" => $item["id"])),
                        "label" => "Delete "
                    ];
                }
            }
        }
        return $dataSource;
    }
}
