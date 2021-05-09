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

namespace Mageplaza\Barcode\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid\Grid as ResultGrid;

/**
 * Class Grid
 * @package Mageplaza\Barcode\Controller\Adminhtml\Import
 */
class Grid extends Action
{
    /**
     * @var PageFactory
     */
    protected $_pageFactory;

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * Grid constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->_pageFactory   = $pageFactory;
        $this->_layoutFactory = $layoutFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|string
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('firstLoad')) {
            return $this->_layoutFactory->create();
        }

        $page = $this->_pageFactory->create();
        $html = $page->getLayout()->createBlock(ResultGrid::class)->toHtml();

        return $this->getResponse()->representJson($html);
    }
}
