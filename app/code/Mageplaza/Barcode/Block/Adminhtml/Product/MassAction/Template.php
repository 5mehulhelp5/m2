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

namespace Mageplaza\Barcode\Block\Adminhtml\Product\MassAction;

use Magento\Backend\Block\Template as BlockTemplate;
use Magento\Backend\Block\Template\Context;
use Mageplaza\Barcode\Block\Adminhtml\Product\Form\PaperPreview;

/**
 * Class Template
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\MassAction
 */
class Template extends BlockTemplate
{
    /**
     * @var PaperPreview
     */
    protected $_paperPreview;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Barcode::product/massaction/form.phtml';

    /**
     * Template constructor.
     *
     * @param Context $context
     * @param PaperPreview $paperPreview
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaperPreview $paperPreview,
        array $data = []
    ) {
        $this->_paperPreview = $paperPreview;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->_paperPreview->getAjaxUrl();
    }
}
