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

namespace Mageplaza\Barcode\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\Barcode\Helper\Pdf;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 * @package Mageplaza\Barcode\Test\Unit\Helper
 */
class PdfTest extends TestCase
{
    /**
     * @var Pdf
     */
    protected $_pdf;

    /**
     * Test Get Template Base Specification
     */
    public function testGetTemplateBaseSpecs()
    {
        $margin       = [
            'top'    => 28.575,
            'right'  => 9.4615,
            'bottom' => 28.575,
            'left'   => 9.4615,
        ];
        $actualResult = $this->_pdf->getMaxItemsPerPage('Letter', 60.325, 31.75, 0, $margin, 6.34, 8.001, 'P');
        $expectResult = 10;
        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $helper     = new ObjectManager($this);
        $this->_pdf = $helper->getObject(Pdf::class);
    }
}
