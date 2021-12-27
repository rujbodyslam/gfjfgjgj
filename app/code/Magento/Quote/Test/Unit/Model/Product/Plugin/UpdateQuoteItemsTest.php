<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Product\Plugin;

use Magento\Catalog\Model\Product;

class UpdateQuoteItemsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Quote\Model\Product\Plugin\UpdateQuoteItems
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Quote\Model\ResourceModel\Quote
     */
    private $quoteResource ;

    protected function setUp()
    {
        $this->quoteResource = $this->getMockBuilder(\Magento\Quote\Model\ResourceModel\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Quote\Model\Product\Plugin\UpdateQuoteItems($this->quoteResource);
    }

    /**
     * @dataProvider aroundUpdateDataProvider
     * @param int $originalPrice
     * @param int $newPrice
     * @param bool $callMethod
     */
    public function testBeforeUpdate($originalPrice, $newPrice, $callMethod)
    {
        $productResourceMock = $this->getMock(\Magento\Catalog\Model\ResourceModel\Product::class, [], [], '', false);
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productId = 1;
        $productMock->expects($this->any())->method('getOrigData')->with('price')->willReturn($originalPrice);
        $productMock->expects($this->any())->method('getPrice')->willReturn($newPrice);
        $productMock->expects($this->any())->method('getId')->willReturn($productId);
        $this->quoteResource->expects($this->$callMethod())->method('markQuotesRecollect')->with($productId);
        $result = $this->model->beforeSave($productResourceMock, $productMock);
        $this->assertEquals($result, NULL);
    }

    public function aroundUpdateDataProvider()
    {
        return [
            [10, 20, 'once'],
            [null, 10, 'never'],
            [10, 10, 'never']
        ];
    }
}
