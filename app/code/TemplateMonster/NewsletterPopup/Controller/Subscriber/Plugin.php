<?php

namespace TemplateMonster\NewsletterPopup\Controller\Subscriber;

use TemplateMonster\NewsletterPopup\Helper\Data as PopupHelper;
use Magento\Newsletter\Controller\Subscriber as SubscriberAction;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Subscriber controller AJAX plugin.
 *
 * @package TemplateMonster\NewsletterPopup\Controller\Subscriber
 */
class Plugin
{
    /**
     * @var PopupHelper
     */
    protected $popupHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * Plugin constructor.
     *
     * @param PopupHelper $popupHelper
     * @param JsonHelper  $jsonHelper]
     */
    public function __construct(PopupHelper $popupHelper, JsonHelper $jsonHelper)
    {
        $this->popupHelper = $popupHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param SubscriberAction $subject
     * @param mixed            $result
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function afterExecute(SubscriberAction $subject, $result)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $subject->getRequest();
        /** @var \Magento\Framework\App\Response\Http $response */
        $response = $subject->getResponse();

        if (!$this->popupHelper->isEnabled() || !$request->isAjax()) {
            return $result;
        }

        // cancel redirect that was sent by action
        if ($response->isRedirect()) {
            $response
                ->clearHeader('Location')
                ->setStatusCode(200);
        }

        return $response->representJson(
            $this->jsonHelper->jsonEncode([
                'success' => true
            ])
        );
    }
}