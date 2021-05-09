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
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class Download
 * @package Mageplaza\Barcode\Controller\Adminhtml\Import
 */
class Download extends Action
{
    const FILE_NAME = 'mageplaza_barcode_sample.csv';

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Download constructor.
     *
     * @param Context $context
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        HelperData $helperData
    ) {
        $this->_fileSystem  = $filesystem;
        $this->_fileFactory = $fileFactory;
        $this->_helperData  = $helperData;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws FileSystemException
     * @throws Exception
     */
    public function execute()
    {
        $tempName = time();
        $this->_fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)->create('mageplaza/barcode');
        $file         = DirectoryList::VAR_DIR . '/mageplaza/barcode/' . $tempName . '.csv';
        $firstProduct = $this->_helperData->getPrintProducts(1)[0];

        $stream = $this->_fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ->openFile($file, 'w+');
        $stream->lock();
        $data = [
            ['sku', 'qty'],
            [$firstProduct['sku'], '10']
        ];
        foreach ($data as $row) {
            $stream->writeCsv($row);
        }
        $stream->unlock();
        $stream->close();

        return $this->_fileFactory->create(
            self::FILE_NAME,
            [
                'type'  => 'filename',
                'value' => $file,
                'rm'    => true
            ],
            DirectoryList::VAR_DIR
        );
    }
}
