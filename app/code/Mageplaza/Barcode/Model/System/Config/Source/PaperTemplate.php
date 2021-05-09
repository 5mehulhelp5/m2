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

namespace Mageplaza\Barcode\Model\System\Config\Source;

/**
 * Class PaperTemplate
 * @package Mageplaza\Barcode\Model\System\Config\Source
 */
class PaperTemplate extends OptionArray
{
    const OL161     = 'OL161';
    const OL160     = 'OL160';
    const OL171     = 'OL171';
    const CUSTOM    = 'custom';
    const BASE_PATH = 'mpbarcode/barcode/paper/';

    /**
     * @var array
     */
    public $specs = [];

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::OL161  => __('Template OL161'),
            self::OL160  => __('Template OL160'),
            self::OL171  => __('Template OL171'),
            self::CUSTOM => __('Custom'),
        ];
    }

    /**
     * @param $template
     *
     * @return array
     */
    public function getTemplateBaseSpecs($template)
    {
        switch ($template) {
            case self::OL160:
                $this->specs = [
                    'size'       => 'Letter',
                    'width'      => 60.325,
                    'height'     => 31.75,
                    'padding'    => 0,
                    'margin'     => '28.575;9.4615;28.575;9.4615',
                    'vertical'   => 6.34,
                    'horizontal' => 8.001,
                    'orient'     => 'P',
                ];
                break;
            case self::OL161:
                $this->specs = [
                    'size'       => 'Letter',
                    'width'      => 95.25,
                    'height'     => 31.75,
                    'padding'    => 0,
                    'margin'     => '29.4513;9.525;29.4513;9.525',
                    'vertical'   => 5.99948,
                    'horizontal' => 6.35,
                    'orient'     => 'P',
                ];
                break;
            case self::OL171:
                $this->specs = [
                    'size'       => 'Letter',
                    'width'      => 95.25,
                    'height'     => 50.7,
                    'padding'    => 0,
                    'margin'     => '28.575;9.525;28.575;9.525',
                    'vertical'   => 6.35,
                    'horizontal' => 6.35,
                    'orient'     => 'P',
                ];
                break;
            default:
                $this->specs = [
                    'size'       => 'A4',
                    'width'      => 100,
                    'height'     => 50,
                    'padding'    => 0,
                    'margin'     => '1;1;1;1',
                    'vertical'   => 0,
                    'horizontal' => 0,
                    'orient'     => 'P',
                ];
                break;
        }

        return $this->specs;
    }

    /**
     * @param string $paperTemplate
     * @param string $labelTemplate
     *
     * @return array
     */
    public function getBarcodeBaseSpecs($paperTemplate, $labelTemplate)
    {
        if ($paperTemplate === self::OL161 && $labelTemplate === LabelTemplate::QR_CODE) {
            return [
                'width'  => 0.9,
                'height' => 0.9,
                'size'   => 0.9,
            ];
        }

        if ($paperTemplate === self::OL160) {
            return [
                'width'  => 0.8,
                'height' => 0.8,
                'size'   => 0.7,
            ];
        }

        if ($paperTemplate === self::OL171) {
            switch ($labelTemplate) {
                case LabelTemplate::QR_CODE:
                    return [
                        'width'  => 1,
                        'height' => 1.5,
                        'size'   => 1,
                    ];
                case LabelTemplate::STANDARD:
                    return [
                        'width'  => 1.2,
                        'height' => 1.8,
                        'size'   => 1,
                    ];
                default:
                    return [
                        'width'  => 1.2,
                        'height' => 1.5,
                        'size'   => 1,
                    ];
            }
        }

        return [
            'width'  => 1,
            'height' => 1,
            'size'   => 1,
        ];
    }

    /**
     * @return array
     */
    public function getPaperConfigPath()
    {
        return [
            'size'       => self::BASE_PATH . 'paper_size',
            'width'      => self::BASE_PATH . 'width',
            'height'     => self::BASE_PATH . 'height',
            'padding'    => self::BASE_PATH . 'padding',
            'margin'     => self::BASE_PATH . 'margin',
            'vertical'   => self::BASE_PATH . 'vertical',
            'horizontal' => self::BASE_PATH . 'horizontal',
            'orient'     => self::BASE_PATH . 'orientation',
        ];
    }
}
