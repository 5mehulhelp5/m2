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

namespace Mageplaza\Barcode\Block\Adminhtml\Barcode\ImportBarcode;

use Mageplaza\Barcode\Block\Adminhtml\Barcode\AbstractContainer;

/**
 * Class Edit
 * @package Mageplaza\Barcode\Block\Adminhtml\Barcode\ImportBarcode
 */
class Edit extends AbstractContainer
{
    /**
     * @return string
     */
    public function getFormId()
    {
        return 'mp_barcode_import_modal_form';
    }

    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_controller = 'adminhtml_barcode_importBarcode';
    }
}
