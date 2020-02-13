<?php

namespace TemplateMonster\SampleDataInstaller\Plugin\Widget\Model\Widget;

use \Magento\Widget\Model\Widget\Instance;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Widget Instance plugin.
 *
 * @package TemplateMonster\SampleDataInstaller\Plugin\Widget\Model\Widget\
 */
class InstancePlugin
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * InstancePlugin constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        Json $serializer = null
    ) {
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * Set widget parameters
     *
     * @return object
     */
    public function aroundBeforeSave(Instance $subject, callable $proceed)
    {
        $widgetParameters = $subject->getData('widget_parameters');
        $result = $proceed();
        $result->setData('widget_parameters', $this->serializer->serialize($widgetParameters));
        return $result;
    }

    /**
     * Get widget parameters
     *
     * @return array
     */

    public function aroundGetWidgetParameters(Instance $subject, callable $proceed)
    {
        $widgetParameters = $subject->getData('widget_parameters');
        if (is_string($widgetParameters)) {
            $widgetParameters = $this->serializer->unserialize($widgetParameters);

            if (is_string($widgetParameters)) {
                $widgetParameters = json_decode($widgetParameters, true);
            }
            return $widgetParameters;
        } elseif (null === $widgetParameters) {
            return [];
        }
        return is_array($widgetParameters) ? $widgetParameters : $proceed();

    }


}