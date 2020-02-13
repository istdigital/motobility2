<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Core\Blocks;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Session;

class SessionScript extends Template
{
    /**
     * @var Session
     */
    protected $session;

    public function __construct(Template\Context $context, Session $session, array $data = []) {
        parent::__construct($context, $data);
        $this->session = $session;
    }

    public function getSaveUrl() {
        return $this->getUrl('mana_core/session/save');
    }

    public function getValues() {
        if (!($values = $this->session->getData('mana'))) {
            $values = (object)[];
        }

        return json_encode($values);
    }
}