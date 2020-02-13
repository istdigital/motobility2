<?php

namespace TemplateMonster\Parallax\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Parallax block grid actions.
 */
class ParallaxBlockActions extends Column
{
    /**
     * Edit block url.
     *
     * @var string
     */
    const BLOCK_URL_PATH_EDIT = 'parallax/block/edit';

    /**
     * Delete block url.
     */
    const BLOCK_URL_PATH_DELETE = 'parallax/block/delete';

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * ParallaxBlockActions constructor.
     *
     * @param UrlInterface       $urlBuilder
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['block_id'])) {
                    $item[$name]['edit'] = [
                        'label' => __('Edit'),
                        'href' => $this->_urlBuilder->getUrl(self::BLOCK_URL_PATH_EDIT, [
                            'block_id' => $item['block_id'],
                        ]),
                    ];
                    $item[$name]['delete'] = [
                        'label' => __('Delete'),
                        'href' => $this->_urlBuilder->getUrl(self::BLOCK_URL_PATH_DELETE, [
                            'block_id' => $item['block_id'],
                        ]),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.name }"'),
                            'message' => __('Are you sure you wan\'t to delete a "${ $.$data.name }" record?'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
