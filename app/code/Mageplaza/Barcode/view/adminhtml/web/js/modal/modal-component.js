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
    'Magento_Ui/js/modal/modal-component',
    'Mageplaza_Barcode/js/barcode/helper',
    'mage/translate'
], function ($, modalComponent, barcodeHelper, $t) {
    'use strict';

    return modalComponent.extend({
        defaults: {
            imports: {
                productName: '${$.provider}:data.product.name',
                productId: '${$.provider}:data.product.current_product_id',
                qty: '${$.provider}:data.product.barcode_data.qty',
                type: '${$.provider}:data.product.barcode_data.barcode_type',
                labelTemplate: '${$.provider}:data.product.barcode_data.barcode_label_template',
                paperTemplate: '${$.provider}:data.product.barcode_data.paper_template',
                ajaxUrl: '${$.provider}:data.product.barcode_data.submit_url'
            }
        },

        printBarcode: function () {
            var self = this;

            this.elems().forEach(this.validate, this);
            if (this.valid) {
                $.ajax({
                    url: self.ajaxUrl,
                    type: 'POST',
                    data: {
                        id: self.productId,
                        qty: self.qty,
                        type: self.type,
                        label_template: self.labelTemplate,
                        paper_template: self.paperTemplate
                    },
                    dataType: 'json',
                    showLoader: true,
                    success: function (response) {
                        if (response.error) {
                            barcodeHelper.alertError($t('Error'), response.message);
                        } else {
                            barcodeHelper.displayPdf(response.data);
                            barcodeHelper.downloadPdf(response.data, self.productName.replace(/\s/g, ''));
                        }
                    },
                    error: function (error) {
                        barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                    }
                });
            }

        }
    });
});