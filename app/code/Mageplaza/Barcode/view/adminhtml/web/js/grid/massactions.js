/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (TreeMassActions) {
        return TreeMassActions.extend({
            togglePrintBarcodeModal: function () {
                var barcodeModal = $('#mp-barcode-print-modal');

                barcodeModal.modal({
                    type: 'slide',
                    title: $t('Print Barcode Label'),
                    innerScroll: true,
                    modalClass: 'mp-barcode-print-action-box',
                    buttons: []
                });
                barcodeModal.trigger('openModal');
            },

            toggleImportBarcodeModal: function () {
                var barcodeModal = $('#mp-barcode-import-modal');

                barcodeModal.modal({
                    type: 'slide',
                    title: $t('Print Import Barcode Label'),
                    innerScroll: true,
                    modalClass: 'mp-barcode-print-action-box',
                    buttons: []
                });
                barcodeModal.trigger('openModal');
            }
        });
    };
});
