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

    $.widget('mpBarcode.load', {
        options: {
            ajaxUrl: '',
            btn: '#mpbarcode_barcode_paper_btn_load',
            paper_template: '#mpbarcode_barcode_paper_paper_template'
        },

        _create: function () {
            var self     = this,
                template = $(this.options.paper_template);

            this._toggleDisable(template.val());

            $(this.options.btn).on('click', function () {
                self._getTemplate();
            });

            template.on('change', function () {
                self._toggleDisable($(this).val());
            });
        },

        _getTemplate: function () {
            var self           = this,
                barcodeSizeRow = $('#row_mpbarcode_barcode_label_size');

            $.ajax({
                url: self.options.ajaxUrl,
                type: 'POST',
                data: {
                    paper: $(self.options.paper_template).val()
                },
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response.error) {
                        barcodeHelper.alertError($t('Error'), response.message);
                    } else {
                        $.each(barcodeHelper.config.paper, function (key, config) {
                            $(config).val(response.paper[key]);
                        });
                        $.each(barcodeHelper.config.label, function (key, config) {
                            $(config).val(response.label[key]);
                        });
                        barcodeSizeRow.hide();
                    }
                },
                error: function (error) {
                    barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        },

        _toggleDisable: function (template) {
            var templateDetailLink = $('#mpbarcode_paper_template_detail_url');

            if (template === barcodeHelper.customTemplate) {
                $.each(barcodeHelper.config.paper, function (key, config) {
                    $(config).removeAttr('disabled');
                    templateDetailLink.hide();
                });
            } else {
                $.each(barcodeHelper.config.paper, function (key, config) {
                    $(config).attr('disabled', 'disabled');
                    templateDetailLink.attr('href', barcodeHelper.templateDetailUrl[template]).show();
                });
            }
        }
    });

    return $.mpBarcode.load;
});
