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

namespace Mageplaza\Barcode\Test\Unit\Controller\Adminhtml\Product;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\Barcode\Controller\Adminhtml\Product\MassPrint;
use PHPUnit\Framework\TestCase;

/**
 * Class MassPrintTest
 * @package Mageplaza\Barcode\Test\Unit\Controller\Adminhtml\Product
 */
class MassPrintTest extends TestCase
{
    /**
     * @var MassPrint
     */
    protected $_massPrint;

    /**
     * Test Get Template Base Specification
     */
    public function testGetTemplateBaseSpecs()
    {
        $params       = [
            'mp_barcode' => [
                'qty'                    => 10,
                'barcode_type'           => 'C93',
                'barcode_label_template' => 'classic1',
                'paper_template'         => 'OL160',
            ],
            'productIds' => [1, 2, 3],
        ];
        $actualResult = $this->_massPrint->processParameters($params);
        $expectResult = [
            'qtyPerItem'     => 10,
            'type'           => 'C93',
            'label_template' => 'classic1',
            'paper_template' => 'OL160',
        ];
        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $helper           = new ObjectManager($this);
        $this->_massPrint = $helper->getObject(MassPrint::class);
    }
}
