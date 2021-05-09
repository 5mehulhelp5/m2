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

namespace Mageplaza\Barcode\Block\Adminhtml\Barcode\Element;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class AbstractButton
 * @package Mageplaza\Barcode\Block\Adminhtml\Barcode\Element
 */
abstract class AbstractButton extends AbstractElement
{
    const PREVIEW_BUTTON = 'Preview Paper Template';
    const CHECK_BUTTON   = 'Check and Show';

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $buttonName = $this->getButtonName();
        $buttonId   = $buttonName === self::CHECK_BUTTON ? 'mp_barcode_check_btn' : 'mp_barcode_preview_paper_btn';

        $buttonHtml = '<div class="pp-buttons-container">';
        $buttonHtml .= '<button type="button" ';
        $buttonHtml .= "id='{$buttonId}' ";
        $buttonHtml .= 'class="action-default save primary">';
        $buttonHtml .= $buttonName === self::CHECK_BUTTON ? __('Check and Show') : __('Preview Paper Template');
        $buttonHtml .= '</button></div>';

        return $buttonHtml;
    }

    /**
     * @return string
     */
    abstract public function getButtonName();
}
