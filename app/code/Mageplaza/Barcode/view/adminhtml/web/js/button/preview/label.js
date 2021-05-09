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

    $.widget('mpBarcode.previewLabel', {
        options: {
            ajaxUrl: '',
            loadUrl: '',
            previewButton: '#mpbarcode_barcode_label_btn_preview_label',
            paperTemplate: '#mpbarcode_barcode_paper_paper_template',
            previewSection: '#mp_barcode_barcode_label_preview'
        },

        _create: function () {
            var self  = this,
                label = barcodeHelper.config.label,
                paper = barcodeHelper.config.paper;

            $(label.template).on('change', function () {
                self._loadLabel($(self.options.paperTemplate).val(), $(label.template).val());
            });

            $(this.options.previewButton).on('click', function () {
                var paperLabel = {
                        width: $(paper.width).val(),
                        height: $(paper.height).val(),
                        padding: $(paper.padding).val()
                    },
                    barcode    = {
                        width: $(label.width).val(),
                        height: $(label.height).val(),
                        size: $(label.size).val()
                    };

                self._previewLabel(paperLabel, barcode);
            });
        },

        /**
         * Load New Label Template when Barcode Label Template changes
         * @param paper
         * @param label
         * @private
         */
        _loadLabel: function (paper, label) {
            var self = this;

            $.ajax({
                url: self.options.loadUrl,
                type: 'POST',
                data: {
                    paper: paper,
                    label: label
                },
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response.error) {
                        barcodeHelper.alertError($t('Error'), response.message);
                    } else {
                        $.each(barcodeHelper.config.label, function (key, config) {
                            if (key !== 'template') {
                                $(config).val(response.label[key]);
                            }
                        });
                    }
                },
                error: function (error) {
                    barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        },

        _previewLabel: function (paperLabel, barcode) {
            var self      = this,
                label     = barcodeHelper.config.label,
                labelHtml = $(label.html).val(),
                iframe    = $(this.options.previewSection + ' iframe');

            if (this._validateData(labelHtml)) {
                $.ajax({
                    url: self.options.ajaxUrl,
                    type: 'POST',
                    data: {
                        html: labelHtml,
                        css: $(label.css).val(),
                        label: paperLabel,
                        barcode: barcode
                    },
                    dataType: 'json',
                    showLoader: true,
                    success: function (response) {
                        if (response.error) {
                            barcodeHelper.alertError($t('Preview Error'), response.message);
                        } else {
                            $(self.options.previewSection).css('height', '460px');
                            $(iframe).attr('src', 'data:application/pdf;base64,' + response.data);
                            $(iframe).show();
                        }

                    },
                    error: function (error) {
                        barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                    }
                });
            }
        },

        _validateData: function (label) {
            var blank = /^\s*$/;

            if (blank.test(label)) {
                barcodeHelper.alertError(
                    $t('Barcode Label Content Is Empty'),
                    $t('Load Default Template or Fill In Content to Preview')
                );
            } else {
                return true;
            }
        }
    });

    return $.mpBarcode.previewLabel;
});
