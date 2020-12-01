define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'credix',
                component: 'Vexsoluciones_Credix/js/view/payment/method-renderer/credix-method'
            }
        );
        return Component.extend({});
    }
);
