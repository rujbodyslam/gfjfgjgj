<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Checkout\Test\Block\Onepage\Shipping\AddressModal;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Checkout shipping address block.
 */
class Shipping extends Form
{
    /**
     * CSS Selector for "New Address" button
     *
     * @var string
     */
    private $newAddressButton = '[data-bind*="isNewAddressAdded"]';

    /**
     * Wait element.
     *
     * @var string
     */
    private $waitElement = '.loading-mask';

    /**
     * SCC Selector for Address Modal block.
     *
     * @var string
     */
    private $addressModalBlock = '//*[@id="opc-new-shipping-address"]/../..';

    /**
     * @var string
     */
    private $selectedAddress = '.shipping-address-item.selected-item';

    /**
     * Click on "New Address" button.
     *
     * @return void
     */
    public function clickOnNewAddressButton()
    {
        $this->waitForElementNotVisible($this->waitElement);
        $this->_rootElement->find($this->newAddressButton)->click();
    }

    /**
     * Get Address Modal Block.
     *
     * @return AddressModal
     */
    public function getAddressModalBlock()
    {
        return $this->blockFactory->create(
            AddressModal::class,
            ['element' => $this->browser->find($this->addressModalBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * @return array
     */
    public function getSelectedAddress()
    {
        return $this->_rootElement->find($this->selectedAddress, Locator::SELECTOR_CSS)->getText();
    }
}
