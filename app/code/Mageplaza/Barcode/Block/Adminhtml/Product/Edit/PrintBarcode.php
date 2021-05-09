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

namespace Mageplaza\Barcode\Block\Adminhtml\Product\Edit;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class PrintBarcode
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\Edit
 */
class PrintBarcode extends Generic
{
    const SUBMIT_URL  = 'mpbarcode/product/printbarcode';
    const PREVIEW_URL = 'mpbarcode/product/previewlabel';

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * PrintBarcode constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param HelperData $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Context $context,
        Registry $registry,
        HelperData $helperData,
        RequestInterface $request
    ) {
        $this->_helperData = $helperData;
        $this->_request    = $request;

        parent::__construct($context, $registry);
    }

    /**
     * Return button attributes array
     *
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->_helperData->isEnabled() || $this->_request->getFullActionName() !== 'catalog_product_edit') {
            return [];
        }

        return [
            'label'          => __('Print Barcode'),
            'class'          => 'action-secondary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form.mp_barcode_modal',
                                'actionName' => 'toggleModal'
                            ],
                        ]
                    ]
                ]
            ],
            'on_click'       => '',
            'sort_order'     => 30,
        ];
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getAjaxUrl($url)
    {
        return $this->getUrl($url);
    }
}
