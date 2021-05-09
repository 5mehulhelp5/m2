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

namespace Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid\Renderer\Qty;

/**
 * Class Grid
 * @package Mageplaza\Barcode\Block\Adminhtml\Product\Import\Grid
 */
class Grid extends Extended
{
    /**
     * @var ProductCollectionFactory
     */
    protected $_productColFactory;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * Grid constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param ProductCollectionFactory $productColFactory
     * @param Session $catalogSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        ProductCollectionFactory $productColFactory,
        Session $catalogSession,
        array $data = []
    ) {
        $this->_productColFactory = $productColFactory;
        $this->_catalogSession    = $catalogSession;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpbarcode/import/grid', ['form_key' => $this->getFormKey()]);
    }

    /**
     * @param object $row
     *
     * @return string
     * @SuppressWarnings(Unused)
     */
    public function getRowUrl($row)
    {
        return '';
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mp_barcode_import_result');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setIsCollapsed(false);
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        $products   = $this->getBarcodeProductCollection();
        $productIds = array_keys($products);

        $collection = $this->_productColFactory->create()
            ->addStoreFilter()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('mp_barcode')
            ->addAttributeToFilter('entity_id', $productIds);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    public function getBarcodeProductCollection()
    {
        $products = $this->getRequest()->getParam('products');
        if ($products !== null) {
            $this->_catalogSession->setBarcodeProducts($products);

            return $products;
        }

        return $this->_catalogSession->getBarcodeProducts();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_products', [
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'in_product',
            'values'           => $this->_getSelectedProducts(),
            'align'            => 'center',
            'index'            => 'entity_id'
        ]);
        $this->addColumn('entity_id', [
            'header'           => __('Product ID'),
            'type'             => 'number',
            'index'            => 'entity_id',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id',
        ]);
        $this->addColumn('name', [
            'header' => __('Name'),
            'index'  => 'name',
            'width'  => '50px',
        ]);
        $this->addColumn('sku', [
            'header' => __('Sku'),
            'index'  => 'sku',
            'width'  => '50px',
        ]);
        $this->addColumn('barcode', [
            'header' => __('Mageplaza Barcode'),
            'index'  => 'mp_barcode',
            'width'  => '50px',
        ]);
        $this->addColumn('qty', [
            'filter'         => false,
            'sortable'       => false,
            'header'         => __('Quantity'),
            'renderer'       => Qty::class,
            'name'           => 'qty',
            'type'           => 'input',
            'validate_class' => 'validate-number',
            'index'          => 'qty'
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        return $this->getRequest()->getPost('barcode_products', null);
    }

    /**
     * @param Column $column
     *
     * @return $this|Extended
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() === 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif ($productIds) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
}
