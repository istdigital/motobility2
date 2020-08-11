<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Base
 */


namespace Amasty\Base\Test\Unit\Model\Feed;

use Amasty\Base\Model\Feed\FeedContentProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class FeedContentProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers NewsProcessor::getCurrentScheme
     */
    public function testGetCurrentScheme()
    {
        $objectManager = new ObjectManager($this);

        $baseUrlObject = $this->createMock(\Zend\Uri\Uri::class);
        $baseUrlObject->expects($this->any())->method('getScheme')->willReturnOnConsecutiveCalls('', 'test');

        $contentProvider = $objectManager->getObject(
            FeedContentProvider::class,
            [
                'baseUrlObject' => $baseUrlObject
            ]
        );
        $this->assertEquals('', $contentProvider->getCurrentScheme());
        $this->assertEquals('test://', $contentProvider->getCurrentScheme());
    }
}
