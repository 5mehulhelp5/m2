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
    'Magento_Ui/js/modal/alert'
], function (alert) {
    'use strict';

    return {
        customTemplate: 'custom',
        templateDetailUrl: {
            OL160: 'https://www.onlinelabels.com/templates/ol160-template-microsoft-word',
            OL161: 'https://www.onlinelabels.com/templates/ol161-template',
            OL171: 'https://www.onlinelabels.com/templates/ol171-template'
        },
        config: {
            paper: {
                size: '#mpbarcode_barcode_paper_paper_size',
                width: '#mpbarcode_barcode_paper_width',
                height: '#mpbarcode_barcode_paper_height',
                padding: '#mpbarcode_barcode_paper_padding',
                margin: '#mpbarcode_barcode_paper_margin',
                vertical: '#mpbarcode_barcode_paper_vertical',
                horizontal: '#mpbarcode_barcode_paper_horizontal',
                orient: '#mpbarcode_barcode_paper_orientation'
            },
            label: {
                template: '#mpbarcode_barcode_label_template',
                width: '#mpbarcode_barcode_label_width',
                height: '#mpbarcode_barcode_label_height',
                size: '#mpbarcode_barcode_label_size',
                html: '#mpbarcode_barcode_label_content',
                css: '#mpbarcode_barcode_label_css'
            }
        },

        massAction: {
            barcode: {
                qty: '#mp_barcode_qty',
                custom: '#mp_barcode_custom_print_barcode',
                type: '#mp_barcode_barcode_type',
                paper: '#mp_barcode_paper_template',
                label: '#mp_barcode_barcode_label_template'
            },
            dependCustom: {
                type: '.field-barcode_type',
                label: '.field-barcode_label_template',
                paper: '.field-paper_template',
                previewButton: '.field-preview_button'
            },
            useConfig: {
                type: '#mp_barcode_use_config_barcode_type',
                label: '#mp_barcode_use_config_barcode_label_template',
                paper: '#mp_barcode_use_config_paper_template'
            }
        },

        productEdit: {
            type: 'select[name="product[barcode_data][barcode_type]"]',
            paper: 'select[name="product[barcode_data][paper_template]"]',
            label: 'select[name="product[barcode_data][barcode_label_template]"]'
        },

        /**
         * Display Base 64 PDF String
         * @param base64String
         * @returns {Blob}
         */
        displayPdf: function (base64String) {
            var windowSpecs    = 'dependent=1,locationbar=0,scrollbars=1,menubar=1,resizable=1,' +
                'screenX=50,screenY=50,width=1000,height=1000';
            var htmlContent    = '<iframe width=100% height=100% type="application/pdf"'
                + ' src="data:application/pdf;base64,' + base64String + '"></iframe>';
            var ProductBarcode = window.open('', "_blank", windowSpecs);

            ProductBarcode.document.write(htmlContent);
            ProductBarcode.document.close();
            ProductBarcode.focus();
        },

        /**
         * Download BASE 64 PDF String after display
         * @param base64String
         * @param filename
         */
        downloadPdf: function (base64String, filename) {
            var barcodeAnchor = document.createElement('a');

            barcodeAnchor.href     = 'data:application/pdf;base64,' + base64String;
            barcodeAnchor.download = filename + '.pdf';
            barcodeAnchor.click();
        },

        alertError: function (title, content) {
            alert({
                title: title,
                content: content
            });
        }
    };
});
