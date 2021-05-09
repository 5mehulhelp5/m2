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
    'uiRegistry',
    'mage/translate'
], function ($, barcodeHelper, registry, $t) {
    'use strict';

    $.widget('mpBarcode.massPrintProduct', {
        options: {
            previewUrl: '',
            modal: $('#mp-barcode-print-modal'),
            defaultValue: {
                type: $('#mp_barcode_barcode_type').val(),
                paper: $(barcodeHelper.massAction.barcode.paper).val(),
                label: $(barcodeHelper.massAction.barcode.label).val()
            },
            productIds: $('#mp_barcode_ids'),
            previewPaperBtn: $('#mp_barcode_preview_paper_btn'),
            printBtn: $('#mass_print_barcode'),
            provider: 'product_listing.product_listing.listing_top.listing_massaction'
        },

        _create: function () {
            var self            = this,
                barcode         = barcodeHelper.massAction.barcode,
                barcodeQtyInput = $('#mp_barcode_qty');

            $(barcode.custom).on('change', function () {
                $.each(barcodeHelper.massAction.dependCustom, function (key, field) {
                    $(field).toggle();
                });
            });

            $.each(barcodeHelper.massAction.useConfig, function (key, config) {
                self._applyDefault(key, config);
            });

            this.options.previewPaperBtn.on('click', function () {
                var paperTemplate = $(barcode.paper).val(),
                    labelTemplate = $(barcode.label).val(),
                    barcodeType   = $(barcode.type).val();

                self._previewPaper(paperTemplate, labelTemplate, barcodeType);
            });

            this.options.printBtn.on('click', function () {
                self._validateAndPrint();
            });

            $(window).keydown(function (event) {
                if (event.keyCode === 13 && barcodeQtyInput.is(':focus')) {
                    event.preventDefault();
                    self._validateAndPrint();
                    return false;
                }
            });
        },

        _applyDefault: function (key, config) {
            var self    = this,
                barcode = barcodeHelper.massAction.barcode;

            $(config).on('change', function () {
                if ($(barcode[key]).attr('disabled')) {
                    $(barcode[key]).removeAttr('disabled');
                } else {
                    $(barcode[key]).val(self.options.defaultValue[key]);
                    $(barcode[key]).attr('disabled', 'disabled');
                }
            });
        },

        _previewPaper: function (paperTemplate, LabelTemplate, barcodeType) {
            var self = this;

            $.ajax({
                type: 'POST',
                url: self.options.previewUrl,
                showLoader: true,
                dataType: 'json',
                data: {
                    paper: paperTemplate,
                    label: LabelTemplate,
                    type: barcodeType
                },
                success: function (response) {
                    var iframe = $('#mp_barcode_preview_paper');

                    if (response.error) {
                        barcodeHelper.alertError($t('Error'), response.message);
                    } else {
                        iframe.attr('src', 'data:application/pdf;base64,' + response.data);
                        iframe.css('display', 'block');
                    }
                },
                error: function (error) {
                    barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        },

        _validateAndPrint: function () {
            var selections  = registry.get(this.options.provider).getSelections().selected,
                barcodeForm = $('#mp_barcode_print_modal_form');

            if (barcodeForm.valid()) {
                this._massPrint(barcodeForm, {productIds: selections});
            }
        },

        _massPrint: function (form, productIds) {
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                showLoader: true,
                dataType: 'json',
                data: form.serialize() + '&' + $.param(productIds),
                success: function (response) {
                    if (response.error) {
                        barcodeHelper.alertError($t('Error'), response.message);
                    } else {
                        barcodeHelper.displayPdf(response.data);
                        barcodeHelper.downloadPdf(response.data, 'ProductsBarcodeLabel');
                    }
                },
                error: function (error) {
                    barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        }
    });

    return $.mpBarcode.massPrintProduct;
});