<?php

namespace Yereone\Testimonials\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Yereone\Testimonials\Helper\Data;

class Testimonial extends Template implements BlockInterface
{
    /**
     * @var integer
     */
    const DEFAULT_PER_PAGE_VALUE = 4;

    /**
     * Path to template
     *
     * @var string
     */
    protected $_template = "widget/testimonials_slider.phtml";

    /**
     * @var \Yereone\Testimonials\Model\TestimonialFactory
     */
    protected $testimonialFactory;

    /**
     * Testimonial slider options
     *
     * @var array
     */
    protected $sliderOptions = [
        'dots' => 'boolean',
        'arrows' => 'boolean',
        'infinite' => 'boolean',
        'speed' => 'integer',
        'slidesToShow' => 'integer',
        'slidesToScroll' => 'integer',
        'autoplay' => 'boolean',
        'autoplaySpeed' => 'integer',
    ];

    /**
     * Testimonial slider responsive options
     *
     * @var array
     */
    protected $sliderResponsiveOptions = [];

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Yereone\Testimonials\Helper\Data
     */
    protected $helper;

    /**
     * @param \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory
     * @param Template\Context $context
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Yereone\Testimonials\Model\TestimonialFactory $testimonialFactory,
        Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Yereone\Testimonials\Helper\Data $helper,
        array $data = []
    ) {
        $this->testimonialFactory = $testimonialFactory;
        $this->_imageFactory = $imageFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->getLayoutType() == 'grid') {
            $this->_template = "widget/testimonials_grid.phtml";
        }
        return $this->_template;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getTestimonialsCollection() && $this->getLayoutType() == 'grid' && $this->isPagination()) {
            $limit = $this->getPerPageTestimonialsLimit();
            $pager = $this->getLayout()->createBlock('Yereone\Testimonials\Block\Html\Pager', 'yereone.testimonials.grid')
                ->setAvailableLimit(array($limit=>$limit))
                ->setShowPerPage(true)
                ->setCollection($this->getTestimonialsCollection());
            $this->setChild('pager', $pager);
            $this->getTestimonialsCollection()->load();
        }
        return $this;
    }

    /**
     * Retrieve testimonials collection instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getTestimonialsCollection()
    {
        $collection = $this->testimonialFactory->create()
            ->getCollection()
            ->addFieldToFilter('status_id', 1)
            ->setOrder($this->getData('order_by'), 'DESC');

        if ($this->getLayoutType() == 'grid' && $this->isPagination()) {
            return $this->prepareTestimonialsCollection($collection);
        }
        return $collection;
    }

    /**
     * @param \Yereone\Testimonials\Model\ResourceModel\Testimonial\Collection $collection
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    protected function prepareTestimonialsCollection($collection)
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $collection->setPageSize($this->getPerPageTestimonialsLimit());
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function getWidgetTitle()
    {
        return $this->getData('widget_title');
    }

    /**
     * Check is pagination enabled
     *
     * @return boolean
     */
    public function isPagination()
    {
        if ($this->getData('pagination')) {
            return true;
        }
        return false;
    }

    /**
     * @return integer
     */
    public function getPerPageTestimonialsLimit()
    {
        if ($perPage = $this->getData('per_page')) {
            return (int)$perPage;
        }
        return self::DEFAULT_PER_PAGE_VALUE;
    }

    /**
     * Get number items
     *
     * @return string
     */
    public function getNumberItem()
    {
        return $this->getData('number_item');
    }

    /**
     * Get Layout
     *
     * @return string
     */
    public function getLayoutType()
    {
        return $this->getData('layout_type');
    }

    /**
     * Get slider options
     *
     * @return string
     */
    public function getCarouselConfigJson()
    {
        $sliderOptions = [];
        $breakpoints = [1024, 768, 480];
        foreach ($this->sliderOptions as $option => $type) {
            if ($this->hasData($option)) {
                switch ($type) {
                    case 'integer':
                        $sliderOptions[$option] = (int)$this->getData($option);
                        break;
                    case 'boolean':
                        $sliderOptions[$option] = (bool)$this->getData($option);
                        break;
                }
            }
        }
        if ($this->isResponsive()) {
            foreach ($breakpoints as $breakpoint) {
                switch ($breakpoint) {
                    case 1024 :
                        $this->sliderResponsiveOptions[] = [
                            'breakpoint' => $breakpoint,
                            'settings' => [
                                'slidesToShow' => (int)$this->getData('slides_to_show_desctop'),
                                'slidesToScroll' => (int)$this->getData('slides_to_scroll_desctop')
                            ]
                        ];
                        break;
                    case 768:
                        $this->sliderResponsiveOptions[] = [
                            'breakpoint' => $breakpoint,
                            'settings' => [
                                'slidesToShow' => (int)$this->getData('slides_to_show_tablet'),
                                'slidesToScroll' => (int)$this->getData('slides_to_scroll_tablet')
                            ]
                        ];
                        break;
                    case 480:
                        $this->sliderResponsiveOptions[] = [
                            'breakpoint' => $breakpoint,
                            'settings' => [
                                'slidesToShow' => (int)$this->getData('slides_to_show_mobile'),
                                'slidesToScroll' => (int)$this->getData('slides_to_scroll_mobile')
                            ]
                        ];
                        break;
                }
            }
            $sliderOptions['responsive'] = $this->sliderResponsiveOptions;
        }
        return json_encode($sliderOptions);
    }

    /**
     * Check is slider responsive
     *
     * @return bool
     */
    public function isResponsive()
    {
        return $this->getData('responsive');
    }

    /**
     * Ordering testimonials
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->getData('order_by');
    }

    /**
     * Show testimonial title
     *
     * @return bool
     */
    public function showTitle()
    {
        return $this->getTitle();
    }

    /**
     * Show testimonial rating
     *
     * @return bool
     */
    public function showRating()
    {
        return $this->getRating();
    }

    /**
     * Show testimonial author
     *
     * @return bool
     */
    public function showAuthor()
    {
        return $this->getAuthor();
    }

    /**
     * Show testimonial author job
     *
     * @return bool
     */
    public function showJob()
    {
        return $this->getJob();
    }

    /**
     * Show testimonial author city
     *
     * @return bool
     */
    public function showCity()
    {
        return $this->getCity();
    }

    /**
     * Show testimonial Creation Time
     *
     * @return bool
     */
    public function showCreationTime()
    {
        return $this->getCreationTime();
    }

    /**
     * Show testimonial author company
     *
     * @return bool
     */
    public function showCompany()
    {
        return $this->getCompany();
    }

    /**
     * Show testimonial author image
     *
     * @return bool
     */
    public function showImage()
    {
        return $this->getImage();
    }

    /**
     * Get author image width
     *
     * @return bool
     */
    public function getImageWidth()
    {
        return $this->getData('image_width');
    }

    /**
     * Get author image style
     *
     * @return string
     */
    public function getImageStyle()
    {
        return $this->getData('image_style');
    }

    /**
     * Show socials
     *
     * @return bool
     */
    public function showSocial()
    {
        return $this->getSocial();
    }

    /**
     * @param string $image
     * @return string
     */
    public function getAuthorImage($image)
    {
        if ($image) {
            return $this->resize($image, 150, 150);
        }
        return $this->getViewFileUrl('Yereone_Testimonials::images/avatar_placeholder_large.jpg');
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getUniqueKey()
    {
        $key = md5(uniqid());
        return $key;
    }

    /**
     * Change the avatar size
     *
     * @param string$image
     * @param null|int $width
     * @param null|int $height
     * @return string
     */
    public function resize($image, $width = null, $height = null)
    {
        $absolutePath = $this->_filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath(Data::MEDIA_PATH).$image;
        $imageResized = $this->_filesystem
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath(Data::MEDIA_PATH.'/catch/'.$width.'/').$image;

        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(true);
        $imageResize->keepTransparency(true);
        $imageResize->keepFrame(false);
        $imageResize->keepAspectRatio(true);
        $imageResize->resize($width, $height);

        $destination = $imageResized ;

        $imageResize->save($destination);

        $resizedURL = $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).Data::MEDIA_PATH.'/catch/'.$width.'/'.$image;
        return $resizedURL;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->isModuleEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
