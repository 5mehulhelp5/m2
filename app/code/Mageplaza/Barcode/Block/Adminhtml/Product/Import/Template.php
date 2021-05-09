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

use Magento\Backend\Block\Template as BlockTemplate;

/**
 * Class Template
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\Import
 */
class Template extends BlockTemplate
{
    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Barcode::product/import/form.phtml';

    /**
     * @param $finalController
     *
     * @return string
     */
    public function getAjaxUrl($finalController)
    {
        return $this->getUrl("mpbarcode/import/{$finalController}", ['form_key' => $this->getFormKey()]);
    }
}
