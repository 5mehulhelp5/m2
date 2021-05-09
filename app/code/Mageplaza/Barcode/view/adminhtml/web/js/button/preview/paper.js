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

    $.widget('mpBarcode.previewPaper', {
        options: {
            ajaxUrl: '',
            previewButton: '#mpbarcode_barcode_paper_btn_preview_paper',
            template: '#mpbarcode_barcode_paper_paper_template',
            previewSection: '#mp_barcode_barcode_paper_preview'
        },

        _create: function () {
            var self  = this,
                paper = barcodeHelper.config.paper;

            $(this.options.previewButton).on('click', function () {
                var s  = $(paper.size).val(),
                    w  = $(paper.width).val(),
                    h  = $(paper.height).val(),
                    p  = $(paper.padding).val(),
                    m  = $(paper.margin).val(),
                    vs = $(paper.vertical).val(),
                    hs = $(paper.horizontal).val(),
                    o  = $(paper.orient).val(),
                    t  = $(self.options.template).val();

                self._previewPaper(s, w, h, p, m, vs, hs, o, t);

            });
        },

        _previewPaper: function (size, width, height, padding, margin, vertical, horizontal, orient, template) {
            var self   = this,
                iframe = self.options.previewSection + ' iframe';

            $.ajax({
                url: self.options.ajaxUrl,
                type: 'POST',
                data: {
                    s: size,
                    w: width,
                    h: height,
                    p: padding,
                    m: margin,
                    vs: vertical,
                    hs: horizontal,
                    o: orient,
                    t: template
                },
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response.error) {
                        barcodeHelper.alertError($t('Preview Error'), response.message);
                    } else {
                        $(iframe).attr('src', 'data:application/pdf;base64,' + response.data);
                        $(self.options.previewSection).css('height', '700px');
                        $(iframe).show();
                    }
                },
                error: function (error) {
                    barcodeHelper.alertError($t('Request Error'), error.status + ' ' + error.statusText);
                }
            });
        }
    });

    return $.mpBarcode.previewPaper;
});
