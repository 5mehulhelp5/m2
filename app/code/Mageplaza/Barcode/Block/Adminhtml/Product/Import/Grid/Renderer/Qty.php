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

namespace Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Input as InputRenderer;
use Magento\Framework\DataObject;
use Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid\Grid as ImportGrid;

/**
 * Class Qty
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid\Renderer
 */
class Qty extends InputRenderer
{
    /**
     * @var ImportGrid
     */
    protected $_importGrid;

    /**
     * Qty constructor.
     *
     * @param Context $context
     * @param ImportGrid $importGrid
     * @param array $data
     */
    public function __construct(
        Context $context,
        ImportGrid $importGrid,
        array $data = []
    ) {
        $this->_importGrid = $importGrid;

        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $products  = $this->_importGrid->getBarcodeProductCollection();
        $productId = $row->getData('entity_id');
        $qty       = isset($products[$productId]) ? $products[$productId] : 1;

        $html = '<input type="text" ';
        $html .= 'name="' . $this->getColumn()->getId() . '" ';
        $html .= 'value="' . $qty . '" ';
        $html .= 'class="input-text admin__control-text"/>';

        return $html;
    }
}
