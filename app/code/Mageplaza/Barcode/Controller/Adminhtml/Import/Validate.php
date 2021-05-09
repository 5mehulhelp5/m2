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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Validate
 * @package Mageplaza\Barcode\Controller\Adminhtml\Import
 */
class Validate extends Action
{
    /**
     * @var UploaderFactory
     */
    protected $_uploadFactory;

    /**
     * @var Csv
     */
    protected $_csv;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Validate constructor.
     *
     * @param Context $context
     * @param UploaderFactory $uploaderFactory
     * @param Csv $csv
     * @param Filesystem $filesystem
     * @param Json $json
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        Csv $csv,
        Filesystem $filesystem,
        Json $json,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->_csv               = $csv;
        $this->_uploadFactory     = $uploaderFactory;
        $this->_fileSystem        = $filesystem;
        $this->_json              = $json;
        $this->_productRepository = $productRepository;
        $this->_logger            = $logger;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $requestFile = $this->getRequest()->getFiles();
        if (isset($requestFile['csv'])) {
            $uploader = $this->_uploadFactory->create(['fileId' => 'csv']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            /** @var Read $mediaDirectory */
            $mediaDirectory = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            try {
                $savedFile = $uploader->save($mediaDirectory->getAbsolutePath('/mageplaza/barcode/'));
                $rawData   = $this->_csv->setDelimiter(',')->setEnclosure(',')
                    ->getData($savedFile['path'] . $savedFile['file']);

                return $this->_json->setData($this->processRawData($rawData));
            } catch (Exception $e) {
                return $this->_json->setData($this->returnError($e->getMessage()));
            }
        }

        return $this->_json->setData($this->returnError(__('Missing CSV File')));
    }

    /**
     * @param array $rawData
     *
     * @return array
     */
    public function processRawData($rawData)
    {
        $checkRows   = 0;
        $validRows   = 0;
        $invalidRows = 0;
        $gridData    = [];

        if (count($rawData) < 2) {
            return $this->returnError(__('Invalid CSV File Content'));
        }
        unset($rawData[0]);

        foreach ($rawData as $line => $data) {
            if (count($data) === 2) {
                $productId = $this->validateSku($data[0]);
                $qty       = (int) $data[1];
                if ($qty > 0 && $productId !== false) {
                    $gridData[$productId] = $qty;
                    $validRows++;
                } else {
                    $invalidRows++;
                }
            } else {
                $invalidRows++;
            }
            $checkRows++;
        }

        if (empty($gridData)) {
            return $this->returnError(__('Invalid CSV File Content'));
        }

        $responseArray = [
            'validated' => true,
            'message'   => __(
                'Checked rows: %1, valid rows: %2, invalid rows: %3',
                $checkRows,
                $validRows,
                $invalidRows
            ),
            'gridData'  => $gridData,
        ];

        return $responseArray;
    }

    /**
     * @param string $message
     *
     * @return array
     */
    public function returnError($message)
    {
        return [
            'error'   => true,
            'message' => $message,
        ];
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
}
