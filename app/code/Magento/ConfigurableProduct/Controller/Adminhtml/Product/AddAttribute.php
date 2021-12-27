<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;

class AddAttribute extends Action
{
    /**
     * Authorization level of a basic admin session.
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     */
    protected $productBuilder;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    /**
     * Add "super" attribute from popup window
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout('popup');
        $this->productBuilder->build($this->getRequest());
        $attributeBlock = $this->_view->getLayout()->createBlock(
            \Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\NewAttribute\Product\Created::class
        );
        $this->_addContent($attributeBlock);
        $this->_view->renderLayout();
    }
}
