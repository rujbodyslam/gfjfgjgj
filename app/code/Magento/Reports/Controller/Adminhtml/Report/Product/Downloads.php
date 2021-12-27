<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Controller\Adminhtml\Report\Product;

class Downloads extends \Magento\Reports\Controller\Adminhtml\Report\Product
{
    /**
     * Downloads action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Magento_Downloadable::report_products_downloads'
        )->_addBreadcrumb(
            __('Downloads'),
            __('Downloads')
        )->_addContent(
            $this->_view->getLayout()->createBlock('Magento\Reports\Block\Adminhtml\Product\Downloads')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Downloads Report'));
        $this->_view->renderLayout();
    }
}
