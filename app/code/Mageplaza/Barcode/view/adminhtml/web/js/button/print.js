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
    'jquery',
    'Mageplaza_Barcode/js/barcode/helper',
    'mage/translate'
], function ($, barcodeHelper, $t) {
    'use strict';

    $.widget('mpBarcode.print', {
        options: {
            ajaxUrl: '',
            printBtn: '#mpbarcode_print_btn_print_sample'
        },

        _create: function () {
            var self = this;

            $(this.options.printBtn).on('click', function () {
                $.ajax({
                    url: self.options.ajaxUrl,
                    type: 'POST',
                    data: {
                        form_key: window.FORM_KEY
                    },
                    dataType: 'json',
                    showLoader: true,
                    success: function (response) {
                        if (response.error) {
                            barcodeHelper.alertError($t('Error'), response.message);
                        } else {
                            barcodeHelper.displayPdf(response.data);
                            barcodeHelper.downloadPdf(response.data, 'BarcodeLabelSample');
                        }
                    },
                    error: function (error) {
                        barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                    }
                });
            });
        }
    });

    return $.mpBarcode.print;
});
