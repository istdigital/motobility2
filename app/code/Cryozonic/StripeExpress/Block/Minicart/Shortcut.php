<?php

namespace Cryozonic\StripeExpress\Block\Minicart;

use Cryozonic\StripeExpress\Block\Button as StripeButton;
use Magento\Catalog\Block\ShortcutInterface;

class Shortcut extends StripeButton implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Cryozonic_StripeExpress::minicart_button.phtml';

    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }

    public function setIsShoppingCart($isShoppingCart)
    {
        $this->isShoppingCart = $isShoppingCart;

        if ($isShoppingCart)
            $this->_template = 'Cryozonic_StripeExpress::cart_button.phtml';
        else
            $this->_template = 'Cryozonic_StripeExpress::minicart_button.phtml';
    }

    /**
     * Is Should Rendered
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function shouldRender()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$payment = $objectManager->create('Magento\Payment\Model\MethodInterface');
        $session = $objectManager->create('Magento\Checkout\Model\Session');

        if ($this->getIsCart()) {
            return true;
        }

        return $this->helper->getStoreConfig('payment/cryozonic_stripeexpress/cart_button', $session->getQuote()->getStoreId())
               && $this->isMiniCart;
    }

    /**
     * Render the block if needed
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _toHtml()
    {
        if (!$this->shouldRender()) {
            return '';
        }

        return parent::_toHtml();
    }
}
