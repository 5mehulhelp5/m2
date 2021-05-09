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

use Magento\Catalog\Ui\Component\Product\MassAction as ProductMassAction;

/**
 * Class NewMassAction
 * @package Mageplaza\Barcode\Plugin\Component
 */
class NewMassAction extends MassAction
{
    /**
     * @param ProductMassAction $subject
     */
    public function afterPrepare(ProductMassAction $subject)
    {
        if ($this->_helperData->isEnabled() && $this->_request->getFullActionName() === 'catalog_product_index') {
            $this->addBarcodeMassAction($subject);
        }
    }
}
