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

namespace Mageplaza\Barcode\Console;

use Exception;
use Mageplaza\Barcode\Helper\Data as HelperData;
use Mageplaza\Barcode\Model\ResourceModel\Barcode\Generate as BarcodeGenerate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Barcode
 * @package Mageplaza\Barcode\Console
 */
class Barcode extends Command
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var BarcodeGenerate
     */
    protected $_barcodeGenerate;

    /**
     * Barcode constructor.
     *
     * @param HelperData $helperData
     * @param BarcodeGenerate $barcodeGenerate
     * @param null $name
     */
    public function __construct(
        HelperData $helperData,
        BarcodeGenerate $barcodeGenerate,
        $name = null
    ) {
        $this->_helperData      = $helperData;
        $this->_barcodeGenerate = $barcodeGenerate;

        parent::__construct($name);
    }

    /**
     *  Configuration for Barcode CLI
     */
    protected function configure()
    {
        $this->setName('mpbarcode:autogen');
        $this->setDescription('Mageplaza Barcode Auto Generate');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating Products Barcode Number...');
        $productIds = $this->_helperData->getProductIdsForBarcode();

        try {
            $this->_barcodeGenerate->generateBarcodeAttribute($productIds);
            $total = count($productIds);
            $output->writeln($total . ' Barcode Number(s) Generated Successfully.');
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
            $output->writeln('Generate Fail!');
        }
    }
}
