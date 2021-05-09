<?php
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

namespace Mageplaza\Barcode\Block\Adminhtml\Product\Import;

use Magento\Backend\Block\Widget\Container;

/**
 * Class Button
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\Import
 */
class Button extends Container
{
    /**
     * @return Container
     */
    protected function _prepareLayout()
    {
        $buttonData = [
            'id'             => 'mp_barcode_import_btn',
            'label'          => __('Print Barcode'),
            'class'          => 'add action-secondary',
            'button_class'   => 'primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_listing.product_listing.listing_top.listing_massaction',
                                'actionName' => 'toggleImportBarcodeModal'
                            ],
                        ]
                    ]
                ]
            ],
        ];
        $this->buttonList->add('mp_barcode_import_btn', $buttonData);

        return parent::_prepareLayout();
    }
}
