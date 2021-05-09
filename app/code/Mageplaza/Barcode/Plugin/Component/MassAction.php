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

namespace Mageplaza\Barcode\Plugin\Component;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\Component\AbstractComponent;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class MassAction
 * @package Mageplaza\Barcode\Plugin\Component
 */
abstract class MassAction
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * MassAction constructor.
     *
     * @param HelperData $_helperData
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $_helperData,
        RequestInterface $request
    ) {
        $this->_helperData = $_helperData;
        $this->_request    = $request;
    }

    /**
     * @param AbstractComponent $subject
     */
    public function addBarcodeMassAction($subject)
    {
        $config = $subject->getData('config');
        if (isset($config['actions'])) {
            $config['actions'][] = [
                'component' => 'uiComponent',
                'type'      => 'mp_barcode_print',
                'label'     => 'Print Barcode Label',
                'callback'  => [
                    'provider' => 'product_listing.product_listing.listing_top.listing_massaction',
                    'target'   => 'togglePrintBarcodeModal',
                ]
            ];
        }

        $subject->setData('config', $config);
    }
}
