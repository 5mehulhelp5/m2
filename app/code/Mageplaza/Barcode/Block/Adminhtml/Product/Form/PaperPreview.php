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

namespace Mageplaza\Barcode\Block\Adminhtml\Product\Form;

use Magento\Backend\Block\Template;

/**
 * Class PaperPreview
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\Form
 */
class PaperPreview extends Template
{
    /**
     * @var string
     */
    protected $_template = 'product/form/paper.phtml';

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpbarcode/barcode/previewpaper');
    }
}
