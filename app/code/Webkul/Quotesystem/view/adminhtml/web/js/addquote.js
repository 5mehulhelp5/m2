/**
 * Webkul Quotesystem
 *
 * @category  Webkul
 * @package   Webkul_Quotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "Magento_Ui/js/modal/modal",
    'mage/validation',
    "jquery/file-uploader"
    ], function ($,mageTemplate,alert,modal) {
        'use strict';
        var customerId;
        if( $("#sales_order_create_customer_grid").css('display') == 'block') {
            sessionStorage.clear();
        }
        if (!customerId) {
            customerId = sessionStorage.getItem('customerId');
        }
        let attributeId;
        $.widget(
            'mage.WkAddquote', {
                options: {

                },
                _create: function () {
                    var self = this;
                    window.productConfigure = {};

                    $("#wk_quotesystem_products").css('display','none');
                    $("body").on('click', "._clickable",function () {
                        if (!customerId) {
                            customerId = sessionStorage.getItem('customerId');
                            if (!customerId) {
                                customerId = parseInt($(this).find(".col-entity_id").html());                    
                            }
                            sessionStorage.setItem('customerId',customerId);
                        }
                        $("#sales_order_create_customer_grid").css('display','none');
                        $("#wk_quotesystem_products").css('display','block');
                    });
                    var options = {
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'popup', 
                        validation:{},
                        buttons: [
                            {
                                text: $.mage.__('Submit'),
                                class: 'button',
                                click: function () {
                                    var form = $('#wk_quotesystem_popup');
                                    if ($(form).validation() && $(form).validation('isValid')) {
                                        $.ajax(
                                            {
                                                url: form.attr('action'),
                                                data: form.serialize(),
                                                type: 'post',
                                                // dataType: 'json',
                                                /**
                                                 * @inheritdoc 
                                                 */
                                                beforeSend: function () {
                                                    $('body').trigger('processStart');
                                                },
                                                /**
                                                 * @inheritdoc 
                                                 */
                                                success: function (res) {
                                                    var eventData, parameters;
                                                    sessionStorage.clear();
                                                    $('body').trigger('processStop');
                                                    $(form)[0].reset();
                                                    $(".wk_quote_configurable_options").empty();
                                                    $("#wk_quotesystem_popup").modal('closeModal');
                                                    window.location.href = self.options.quoteUrl
                                                }
                                            }
                                        );
                                    }
                                }
                            },
                            {
                                text: $.mage.__('Reset'),
                                class: 'reset',
                                click: function () {
                                    var form = $('#wk_quotesystem_popup');
                                    $(form)[0].reset();
                                    $(".wk-uploaded-file").remove();
                                }
                            }
                        ]
                    };
                    var popup = modal(options, $('#wk_quotesystem_popup'));
                    $(".wk_quotesystem_column_action").click (
                        function(e) {
                            e.preventDefault();
                            let id = parseInt($(this).siblings(".col-entity_id").html());
                            $(".wk_quote_configurable_options").empty();
                            $.ajax(
                                {
                                    url: self.options.ajaxurl,
                                    data: {id},
                                    type: "get",
                                    success: function(res) {
                                        var productType = res.type;
                                        $.ajax(
                                            {
                                                url: self.options.customOptionsUrl,
                                                data: {id},
                                                type: "post",
                                                success: function (res) {
                                                    $("#catalog_product_composite_configure_fields_configurable").empty();
                                                    $(".wk_quote_configurable_options").prepend(res);
                                                    $("#product_composite_configure_fields_qty").remove();
                                                    // $("#catalog_product_composite_configure_fields_configurable").remove();
                                                    if (productType == 'configurable') {
                                                        var formkey = self.options.formkey;
                                                        $.ajax(
                                                            {
                                                                url: self.options.optionsurl,
                                                                data: {id},
                                                                type: "post",
                                                                success: function (res) {
                                                                    $("#catalog_product_composite_configure_fields_configurable").empty();
                                                                    $("#product_composite_configure_form_fields").prepend(res);
                                                                }
                                                            }
                                                        )
                                                    }
                                                }
                                            }
                                        )
                                        if(res == 'bundle') {
                                            var formkey = self.options.formkey;
                                            alert(
                                                {
                                                    content: $.mage.__("Bundle Product can't be Quoted from Admin end.")
                                                }
                                            );
                                        }
                                        $("#wk_quotesystem_popup").find('input[name="product"]').val(id);
                                        $("#wk_quotesystem_popup").find('input[name="customer_id"]').val(customerId);
                                        $(".wk-uploaded-file").remove();
                                        $("#wk_quotesystem_popup").modal("openModal");
                                    }
                                }
                            ) 
                        }
                    );
                    $('#wk-file-field').fileupload(
                        {
                            dataType: 'json',
                            sequentialUploads: true,
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf|doc|zip)$/i,
                            add: function (e, data) {
                                var progressTmpl = mageTemplate('#wk-file-field-uploader-template'),
                                tmpl;
                                var thisObj = $(this);

                                $.each(
                                    data.files, function (index, file) {
                                        data.fileId = Math.random().toString(33).substr(2, 18);

                                        tmpl = progressTmpl(
                                            {
                                                data: {
                                                    id: data.fileId
                                                }
                                            }
                                        );
                                        if ($('.wk-uploaded-file').length) {
                                            var indexKey = 1;
                                            $('.wk-uploaded-file').each(
                                                function () {
                                                    if (indexKey == 1) {
                                                        $(this).before(tmpl);
                                                    }
                                                    indexKey++;
                                                }
                                            );
                                        } else {
                                            $(tmpl).appendTo('.wk-file-field-container');
                                        }
                                    }
                                );
                                thisObj.fileupload('process', data).done(
                                    function () {
                                        data.submit();
                                    }
                                );
                            },
                            done: function (e, data) {
                                if (data.result && !data.result.error) {
                                    var progressTmpl = mageTemplate('#wk-file-field-template'),
                                    tmpl;
                                    tmpl = progressTmpl(
                                        {
                                            data: {
                                                name: data.result.name,
                                                file: data.result.file,
                                                extension: data.result.type
                                            }
                                        }
                                    );

                                    $(tmpl).appendTo('.wk-file-field-container');
                                } else {
                                    $('#' + data.fileId)
                                    .delay(2000)
                                    .hide('highlight');
                                    alert(
                                        {
                                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                                        }
                                    );
                                }
                                $('#' + data.fileId).remove();
                            },
                            progress: function (e, data) {
                                var progress = parseInt(data.loaded / data.total * 100, 10);
                                var progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';
                                $(progressSelector).css('width', progress + '%');
                            },
                            fail: function (e, data) {
                                var progressSelector = '#' + data.fileId;
                                $(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                                .delay(2000)
                                .hide('highlight')
                                .remove();
                            }
                        }
                    );
                    $('#wk-file-field').fileupload(
                        'option', {
                            process: [{
                                action: 'load',
                                fileTypes: /^image\/(gif|jpe?g|png|pdf|doc|zip)$/
                            }, {
                                action: 'resize',
                                maxWidth: self.options.maxWidth ,
                                maxHeight: self.options.maxHeight
                            }, {
                                action: 'save'
                            }]
                        }
                    );

                    $('#wk-mass-file-field').fileupload(
                        {
                            dataType: 'json',
                            sequentialUploads: true,
                            acceptFileTypes: /(\.|\/)(gif|jpe?g|png|pdf|doc|zip)$/i,
                            add: function (e, data) {
                                var progressTmpl = mageTemplate('#wk-mass-file-field-uploader-template'),
                                tmpl;
                                var thisObj = $(this);

                                $.each(
                                    data.files, function (index, file) {
                                        data.fileId = Math.random().toString(33).substr(2, 18);

                                        tmpl = progressTmpl(
                                            {
                                                data: {
                                                    id: data.fileId
                                                }
                                            }
                                        );
                                        if ($('.wk-uploaded-file').length) {
                                            var indexKey = 1;
                                            $('.wk-uploaded-file').each(
                                                function () {
                                                    if (indexKey == 1) {
                                                        $(this).before(tmpl);
                                                    }
                                                    indexKey++;
                                                }
                                            );
                                        } else {
                                            $(tmpl).appendTo('.wk-file-field-container');
                                        }
                                    }
                                );
                                thisObj.fileupload('process', data).done(
                                    function () {
                                        data.submit();
                                    }
                                );
                            },
                            done: function (e, data) {
                                if (data.result && !data.result.error) {
                                    var progressTmpl = mageTemplate('#wk-mass-file-field-template'),
                                    tmpl;
                                    tmpl = progressTmpl(
                                        {
                                            data: {
                                                name: data.result.name,
                                                file: data.result.file,
                                                extension: data.result.type
                                            }
                                        }
                                    );

                                    $(tmpl).appendTo('.wk-file-field-container');
                                } else {
                                    $('#' + data.fileId)
                                    .delay(2000)
                                    .hide('highlight');
                                    alert(
                                        {
                                            content: $.mage.__('We don\'t recognize or support this file extension type.')
                                        }
                                    );
                                }
                                $('#' + data.fileId).remove();
                            },
                            progress: function (e, data) {
                                var progress = parseInt(data.loaded / data.total * 100, 10);
                                var progressSelector = '#' + data.fileId + ' .progressbar-container .progressbar';
                                $(progressSelector).css('width', progress + '%');
                            },
                            fail: function (e, data) {
                                var progressSelector = '#' + data.fileId;
                                $(progressSelector).removeClass('upload-progress').addClass('upload-failure')
                                .delay(2000)
                                .hide('highlight')
                                .remove();
                            }
                        }
                    );
                    $('#wk-mass-file-field').fileupload(
                        'option', {
                            process: [{
                                action: 'load',
                                fileTypes: /^image\/(gif|jpe?g|png|pdf|doc|zip)$/
                            }, {
                                action: 'resize',
                                maxWidth: self.options.maxWidth ,
                                maxHeight: self.options.maxHeight
                            }, {
                                action: 'save'
                            }]
                        }
                    );

                    $('.wk-file-field-container').on(
                        "click", ".wk-uploaded-file-del", function () {
                            var thisObj = $(this);
                            var fileName = $(this).parent('.wk-uploaded-file').find('.wk-uploaded-file-value').val();
                            $.ajax(
                                {
                                    url: self.options.fileDeleteUrl,
                                    data: { file_name : fileName },
                                    type: "post",
                                    datatype: "json",
                                    showLoader: true,
                                    success: function (data) {
                                        thisObj.parent('.wk-uploaded-file').remove();
                                    },
                                    error: function (data) {
                                        thisObj.parent('.wk-uploaded-file').remove();
                                    }
                                }
                            );
                        }
                    );

                // updation
                    let id;
                    var modaloptions = {
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'popup', 
                        validation:{},
                        buttons: [
                            {
                                text: $.mage.__('Ok'),
                                class: 'button primary',
                                click: function () {
                                    let productId = id;
                                    var array = {};
                                    $.each($(".admin__field-control input[type='hidden']"), function(key, value) {
                                        let attributeName = parseInt(value.name, 10);
                                        var attributeValue = parseInt(value.value, 10);
                                        
                                        if(attributeName !== null && attributeValue !== null) {
                                            array[attributeName] = attributeValue;
                                        }
                                        
                                    });
                                    if (!$.isEmptyObject(array)) {
                                        $.each(array, function(key, value) {

                                            if(value === null || isNaN(value)) {
                                                alert(
                                                    {
                                                        content: $.mage.__("Options are not selected")
                                                    }
                                                );
                                                $("#"+attributeId).prop("checked", false);
                                                return false;
                                            }
                                        });
                                        localStorage.setItem(productId, JSON.stringify(array));
                                    }
                                    
                                    var customoptionArr = {};
                                    $.each($(".product-custom-option"), function(key, value) {
                                        if (value.value != "") {
                                            customoptionArr[value.name] = value.value;
                                        } else {
                                            alert(
                                                {
                                                    content: $.mage.__("Please Enter Custom Options.")
                                                }
                                            );
                                            $("#"+attributeId).prop("checked", false);
                                            return false;
                                        }
                                    });
                                    if (!$.isEmptyObject(customoptionArr)) {
                                        $.each(customoptionArr, function(key, value) {

                                            if(value === null) {
                                                alert(
                                                    {
                                                        content: $.mage.__("Please Enter Custom Options.")
                                                    }
                                                );
                                                $("#"+attributeId).prop("checked", false);
                                                return false;
                                            }
                                        });
                                        localStorage.setItem("customOption"+productId, JSON.stringify(customoptionArr));
                                    }

                                    $("#wk_open_modal").modal('closeModal');
                                }
                            }
                        ]
                    };
                    
                    var popup = modal(modaloptions, $('#wk_open_modal'));
                    $('.action-default').prop("onclick", null).off("click");
                    $(".data-grid-checkbox-cell-inner input[type='checkbox']").on("click", function() {
                        attributeId = $(this).attr("id");
                        id = parseInt($(this).val());
                        $.ajax(
                            {
                                url: self.options.ajaxurl,
                                data: {id},
                                type: "get",
                                beforeSend: function () {
                                    $('body').trigger('processStart');
                                },
                                success: function(res) {
                                    $('body').trigger('processStop');
                                    var productType = res.type;
                                    var hasCustomoption = res.hasCustomOption;
                                    $.ajax(
                                        {
                                            url: self.options.customOptionsUrl,
                                            data: {id},
                                            type: "post",
                                            success: function (res) {
                                                $("#product_composite_configure_form_fields1").empty();
                                                $("#product_composite_configure_form_fields1").prepend(res);
                                                $("#product_composite_configure_fields_qty").remove();
                                                $("#catalog_product_composite_configure_fields_configurable").remove();
                                                if($("#"+attributeId+":checked").length && hasCustomoption
                                                 && productType != "configurable") {
                                                    $("#wk_open_modal").modal("openModal");
                                                }
                                                if (productType == 'configurable') {
                                                    var formkey = self.options.formkey;
                                                    $.ajax(
                                                        {
                                                            url: self.options.optionsurl,
                                                            data: {id},
                                                            type: "post",
                                                            
                                                            success: function (res) {
                                                                $("#catalog_product_composite_configure_fields_configurable").empty();
                                                                $("#product_composite_configure_form_fields1").prepend(res);
                                                            }
                                                        }
                                                    );
                                                    if($("#"+attributeId+":checked").length) {
                                                        $("#wk_open_modal").modal("openModal");
                                                    }
                                                }
                                            }
                                        }
                                    )
                                }
                            }
                        );
                    });
                    $('#wk_open_modal').on('modalclosed', function() { 
                        $(".action-close").on("click", function () {
                            $("#"+attributeId).prop("checked", false);
                        })
                      });

                    var massquoteoptions = {
                        type: 'slide',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'popup', 
                        validation:{},
                        buttons: [
                            {
                                text: $.mage.__('Submit'),
                                class: 'button',
                                click: function () {
                                    var form = $('#wk_quotesystem_massquote_popup');
                                    if ($(form).validation() && $(form).validation('isValid')) {
                                        $.ajax(
                                            {
                                                url: form.attr('action'),
                                                data: form.serialize(),
                                                type: 'post',
                                                // dataType: 'json',
                                                /**
                                                 * @inheritdoc 
                                                 */
                                                beforeSend: function () {
                                                    $('body').trigger('processStart');
                                                },
                                                /**
                                                 * @inheritdoc 
                                                 */
                                                success: function (res) {
                                                    var eventData, parameters;
                                                    $('body').trigger('processStop');
                                                    $(form)[0].reset();
                                                    $(".wk_quote_configurable_options").empty();
                                                    $("#wk_quotesystem_massquote_popup").modal('closeModal');
                                                    window.location.href = self.options.quoteUrl;
                                                }
                                            }
                                        );
                                    }
                                }
                            },
                            {
                                text: $.mage.__('Reset'),
                                class: 'reset',
                                click: function () {
                                    var form = $('#wk_quotesystem_massquote_popup');
                                    $(form)[0].reset();
                                    $(".wk-uploaded-file").remove();
                                }
                            }
                        ]
                    };
                    var popup = modal(massquoteoptions, $('#wk_quotesystem_massquote_popup'));
                    $('.action-default').on("click", function() {
                        var tempArr = [];
                        var producId = 0;
                        if($(".data-grid-checkbox-cell-inner input[type='checkbox']:checked").length) {
                            $(".data-grid-checkbox-cell-inner input[type='checkbox']").each(function() {
                                if ($(this).prop('checked') == true) {
                                    producId = parseInt($(this).parent().parent().next().text());
                                    tempArr.push(producId);

                                    // configurable attribute data
                                    var configData = localStorage.getItem(producId);
                                    if (configData != null) {
                                        if(producId !== null && configData !== null) {
                                            $('#wk_quotesystem_massquote_popup').append(
                                                $('<input/>').attr('type', 'hidden').attr('name', producId).attr('value', configData)
                                            );
                                            localStorage.removeItem(producId);
                                        }
                                    }
                                    //custom option data
                                    var customOptionData = localStorage.getItem("customOption"+producId);
                                    if (customOptionData != null) {
                                        if(producId !== null && customOptionData !== null) {
                                            $('#wk_quotesystem_massquote_popup').append(
                                                $('<input/>').attr('type', 'hidden').attr('name', "customOption"+producId).attr('value', customOptionData)
                                            );
                                            localStorage.removeItem("customOption"+producId);
                                        }
                                    }
                                }
                            })
                            $("#wk_quotesystem_massquote_popup").find('input[name="product_ids"]').val(tempArr);
                            $("#wk_quotesystem_massquote_popup").find('input[name="customer_id"]').val(customerId);
                            $("#wk_quotesystem_massquote_popup").modal("openModal");
                        }

                        
                    })
                }
            }
        )
        return $.mage.WkAddquote;
    }
);
