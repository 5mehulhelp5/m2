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

namespace Mageplaza\Barcode\Test\Unit\Model\System\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Class PaperTemplateTest
 * @package Mageplaza\Barcode\Test\Unit\Model\System\Config\Source
 */
class PaperTemplateTest extends TestCase
{
    /**
     * @var PaperTemplate
     */
    protected $_paperTemplate;

    /**
     * Test Get Template Base Specification
     */
    public function testGetTemplateBaseSpecs()
    {
        $expectResult = [
            'size'       => 'Letter',
            'width'      => 60.325,
            'height'     => 31.75,
            'padding'    => 0,
            'margin'     => '28.575;9.4615;28.575;9.4615',
            'vertical'   => 6.34,
            'horizontal' => 8.001,
            'orient'     => 'P',
        ];
        $actualResult = $this->_paperTemplate->getTemplateBaseSpecs(PaperTemplate::OL160);
        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * Test Get Barcode Base Specification
     */
    public function testGetBarcodeBaseSpecs()
    {
        $expectResult = [
            'width'  => 0.8,
            'height' => 0.8,
            'size'   => 0.7,
        ];
        $actualResult = $this->_paperTemplate->getBarcodeBaseSpecs(PaperTemplate::OL160, null);
        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $helper               = new ObjectManager($this);
        $this->_paperTemplate = $helper->getObject(PaperTemplate::class);
    }
}
