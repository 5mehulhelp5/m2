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

namespace Mageplaza\Barcode\Block\Adminhtml\System\Config\Button;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class ActionButton
 * @package Mageplaza\Barcode\Block\Adminhtml\System\Config\Button
 */
class ActionButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/config/button/actionbutton.phtml';

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * ActionButton constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Unset scope
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope();

        return parent::render($element);
    }

    /**
     * @return bool
     */
    public function isRegenerate()
    {
        return $this->_helperData->isRegenerate() === '1';
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData($this->getButtonData($element));

        return $this->_toHtml();
    }

    /**
     * @param AbstractElement $element
     *
     * @return array
     */
    public function getButtonData($element)
    {
        $systemData = $element->getOriginalData();

        return [
            'button_label' => __($systemData['button_label']),
            'button_url'   => $this->getUrl($systemData['button_url']),
            'html_id'      => $element->getHtmlId(),
            'button_type'  => ($systemData['button_label'] === 'RUN') ? 'basic' : 'primary',
        ];
    }
}
