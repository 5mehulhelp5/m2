/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Barcode
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (checkbox) {
    'use strict';

    return checkbox.extend({
        defaults: {
            valueFromConfig: '',
            linkedValue: ''
        },

        /**
         * @returns {Element}
         */
        initObservable: function () {
            return this
            ._super()
            .observe(['defaultConfig', 'linkedValue']);
        },

        /**
         * @inheritdoc
         */
        onCheckedChanged: function (newChecked) {
            if (newChecked) {
                this.linkedValue(this.defaultConfig());
            }

            this._super(newChecked);
        }
    });
});
