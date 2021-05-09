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

namespace Mageplaza\Barcode\Plugin\Product;

use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class BarcodeAttribute
 * @package Mageplaza\Barcode\Plugin\Product
 */
class BarcodeAttribute
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * MassAction constructor.
     *
     * @param HelperData $_helperData
     */
    public function __construct(HelperData $_helperData)
    {
        $this->_helperData = $_helperData;
    }

    /**
     * @param ProductDataProvider $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetMeta(ProductDataProvider $subject, $result)
    {
        if (!$this->_helperData->isEnabled()) {
            unset($result['product-details']['children']['container_mp_barcode']);
        }

        return $result;
    }
}
