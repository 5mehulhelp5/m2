define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Payment/js/model/credit-card-validation/credit-card-data',
        'Magento_Payment/js/model/credit-card-validation/credit-card-number-validator',
    ],
    function (Component, $, quote, validator, creditCardData, cardNumberValidator) {
        'use strict';

        const GATEWAY_CODE = 'credix';

        return Component.extend({
            defaults: {
                template: 'Vexsoluciones_Credix/payment/credix-form',
                paymentToken: ''
            },
            initObservable: function () {
                this._super()
                    .observe('purchaseOrderNumber');

                return this;
            },
            getCode: function () {
                return GATEWAY_CODE;
            },
            getConfigValue: function(key) {
                return window.checkoutConfig.payment[GATEWAY_CODE][key];
            },
            getPaymentBrandImage: function() {
                return this.getConfigValue('payment_brand_logo');
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            isActive: function () {
                return this.getConfigValue('isActive');
            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'card_number': creditCardData.creditCardNumber,
                        'month': creditCardData.expirationMonth,
                        'year': creditCardData.expirationYear,
                        'cvv': creditCardData.cvvCode,
                        'quota': 1,
                    }
                };
            }
        });
    }
);
