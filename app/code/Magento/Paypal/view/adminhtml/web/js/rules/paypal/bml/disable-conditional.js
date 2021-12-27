/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Paypal/js/rules/paypal/bml/disable'
], function (disable) {
    'use strict';

    return function ($target, $owner, data) {
        if ($target.find(data.enableButton).val() === '0') {
            disable($target, $owner, data);
        }
    };
});
