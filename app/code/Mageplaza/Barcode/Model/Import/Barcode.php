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

namespace Mageplaza\Barcode\Model\Import;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Mageplaza\Barcode\Model\ResourceModel\Barcode\Generate as GenerateBarcode;
use Psr\Log\LoggerInterface;

/**
 * Class Barcode
 * @package Mageplaza\Barcode\Model\Import
 */
class Barcode extends AbstractEntity
{
    const COL_SKU     = 'sku';
    const COL_BARCODE = 'barcode';
    /**
     * Error Codes
     */
    const ERROR_EMPTY_SKU         = 'empty_sku';
    const ERROR_EMPTY_BARCODE     = 'empty_barcode';
    const ERROR_DUPLICATE_BARCODE = 'duplicate_barcode';
    const ERROR_DUPLICATE_SKU     = 'duplicate_sku';
    const ERROR_NOT_EXIST_SKU     = 'not_exist_sku';
    const ERROR_GENERAL           = 'Invalid Row Data';

    /** @inheritdoc */
    protected $masterAttributeCode = 'sku';

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [self::COL_SKU];

    /** @inheritdoc */
    protected $_availableBehaviors = [
        Import::BEHAVIOR_APPEND,
        Import::BEHAVIOR_REPLACE,
        Import::BEHAVIOR_DELETE
    ];

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * @var array
     */
    protected $validColumnNames = [
        self::COL_SKU,
        self::COL_BARCODE,
    ];

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var GenerateBarcode
     */
    protected $_generateBarcode;

    /**
     * @var array
     */
    protected $_importBarcode = [];

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Barcode constructor.
     *
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param ProductRepositoryInterface $productRepository
     * @param GenerateBarcode $generateBarcode
     * @param ProductCollectionFactory $productCollection
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        ProductRepositoryInterface $productRepository,
        GenerateBarcode $generateBarcode,
        ProductCollectionFactory $productCollection,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->_productRepository        = $productRepository;
        $this->_generateBarcode          = $generateBarcode;
        $this->_productCollectionFactory = $productCollection;
        $this->_logger                   = $logger;

        parent::__construct($string, $scopeConfig, $importFactory, $resourceHelper, $resource, $errorAggregator, $data);

        foreach ($this->getErrorCodeMessage() as $code => $message) {
            $this->addMessageTemplate($code, $message);
        }
    }

    /**
     * @return bool
     */
    protected function _importData()
    {
        $behavior = $this->getBehavior();
        $bunch    = $this->_dataSourceModel->getNextBunch();

        $importBarcode = [];
        foreach ($bunch as $rowNum => $rowData) {
            if (!$this->validateRow($rowData, $rowNum)) {
                $this->addRowError(self::ERROR_GENERAL, $rowNum);
                continue;
            }
            if ($this->getErrorAggregator()->hasToBeTerminated()) {
                $this->getErrorAggregator()->addRowToSkip($rowNum);
                continue;
            }

            $productId                 = $this->validateSku($rowData[self::COL_SKU]);
            $barcode                   = $behavior === Import::BEHAVIOR_DELETE ? null : $rowData[self::COL_BARCODE];
            $importBarcode[$productId] = $barcode;
        }

        if ($behavior === Import::BEHAVIOR_APPEND) {
            $importProductIds  = array_keys($importBarcode);
            $productCollection = $this->_productCollectionFactory->create()
                ->addAttributeToFilter('entity_id', $importProductIds)
                ->addAttributeToFilter($this->getEntityTypeCode(), ['null' => true], 'left')
                ->getAllIds();

            $flipCollection          = array_flip($productCollection);
            $importBarcode           = array_diff($importBarcode, array_diff_key($importBarcode, $flipCollection));
            $this->countItemsCreated = count($importBarcode);
        }

        if ($behavior === Import::BEHAVIOR_DELETE) {
            $this->countItemsDeleted = count($importBarcode);
        }

        if ($behavior === Import::BEHAVIOR_REPLACE) {
            $this->countItemsUpdated = count($importBarcode);
        }

        try {
            $this->_generateBarcode->generateBarcodeAttribute($importBarcode, true);

            return true;
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     *
     * @return bool
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (trim($rowData[self::COL_SKU]) === '') {
            $this->addRowError(self::ERROR_EMPTY_SKU, $rowNumber);
        }

        if (trim($rowData[self::COL_BARCODE]) === '') {
            $this->addRowError(self::ERROR_EMPTY_BARCODE, $rowNumber);
        }

        $productId = $this->validateSku($rowData[self::COL_SKU]);
        if ($productId) {
            $productDuplicated = array_key_exists($productId, $this->_importBarcode);
            $barcodeDuplicated = in_array($rowData[self::COL_BARCODE], $this->_importBarcode, true);
            if ($productDuplicated) {
                $this->addRowError(self::ERROR_DUPLICATE_SKU, $rowNumber);
            }
            if ($barcodeDuplicated) {
                $this->addRowError(self::ERROR_DUPLICATE_BARCODE, $rowNumber);
            }
            if (!$barcodeDuplicated && !$productDuplicated) {
                $this->_importBarcode[$productId] = $rowData[self::COL_BARCODE];
            }
        } else {
            $this->addRowError(self::ERROR_NOT_EXIST_SKU, $rowNumber);
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * @param string $sku
     *
     * @return mixed
     */
    public function validateSku($sku)
    {
        try {
            return $this->_productRepository->get($sku)->getId();
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());

            return false;
        }
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'mp_barcode';
    }

    /**
     * @return array
     */
    public function getErrorCodeMessage()
    {
        return [
            self::ERROR_EMPTY_SKU         => __('SKU is empty.'),
            self::ERROR_EMPTY_BARCODE     => __('Barcode is already exist.'),
            self::ERROR_DUPLICATE_BARCODE => __('Barcode is already exist.'),
            self::ERROR_DUPLICATE_SKU     => __('SKU is already exist.'),
            self::ERROR_NOT_EXIST_SKU     => __('Cannot find product with the given SKU.'),
            self::ERROR_GENERAL           => __('Invalid Row Data.'),
        ];
    }
}
