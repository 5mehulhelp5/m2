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

namespace Mageplaza\Barcode\Helper;

use Mageplaza\Barcode\Model\System\Config\Source\PaperOrientation as Orientation;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

/**
 * Class Pdf
 * @package Mageplaza\Barcode\Helper
 */
class Pdf
{
    const LABEL_PREVIEW = 'label_preview';

    /**
     * Base width and height dimension of a Page
     * @var array
     */
    public $dimensions = [
        'P' => [
            'A4'     => [
                'w' => 210.058,
                'h' => 296.926,
            ],
            'A5'     => [
                'w' => 148.082,
                'h' => 210.058,
            ],
            'A6'     => [
                'w' => 104.902,
                'h' => 148.082,
            ],
            'Letter' => [
                'w' => 215.9,
                'h' => 279.4,
            ],
        ],

        'L' => [
            'A4'     => [
                'w' => 296.926,
                'h' => 210.058,
            ],
            'A5'     => [
                'w' => 210.058,
                'h' => 148.082,
            ],
            'A6'     => [
                'w' => 148.082,
                'h' => 104.902,
            ],
            'Letter' => [
                'w' => 279.4,
                'h' => 215.9,
            ],
        ],
    ];

    /**
     * @param string $fileName
     * @param string $html
     * @param string $dest
     * @param string $pageSize
     * @param string $orient
     * @param array $margin
     *
     * @return string
     * @throws MpdfException
     */
    public function exportToPDF($fileName, $html, $dest, $pageSize, $orient, $margin)
    {
        $mPdf = $this->createMpdf($pageSize, $orient, $margin);
        $mPdf->WriteHTML($html);

        return $mPdf->Output($fileName, $dest);
    }

    /**
     * @param string $pageSize
     * @param string $orient
     * @param array $margin
     *
     * @return Mpdf
     * @throws MpdfException
     */
    public function createMpdf($pageSize, $orient, $margin)
    {
        ($orient === Orientation::LANDSCAPE) ? $size = $pageSize . '-' . $orient : $size = $pageSize;
        $config = [
            'mode'          => 'utf-8',
            'format'        => $size,
            'autoPageBreak' => true,
            'margin_top'    => $margin['top'],
            'margin_right'  => $margin['right'],
            'margin_bottom' => $margin['bottom'],
            'margin_left'   => $margin['left'],
            'margin_header' => 0,
            'margin_footer' => 0,
            'orientation'   => $orient,
            'tempDir'       => BP . '/var/tmp'
        ];

        return new Mpdf($config);
    }

    /**
     * @param string $size
     * @param float $width
     * @param float $height
     * @param float $padding
     * @param array $margin
     * @param float $vertical
     * @param float $horizontal
     * @param string $orient
     *
     * @return float|int
     */
    public function getMaxItemsPerPage($size, $width, $height, $padding, $margin, $vertical, $horizontal, $orient)
    {
        $itemWidth     = (float) $width + ($padding * 2) + $horizontal;
        $itemHeight    = (float) $height + ($padding * 2) + $vertical;
        $contentWidth  = $this->dimensions[$orient][$size]['w'] - (float) $margin['right'] - (float) $margin['left'];
        $contentHeight = $this->dimensions[$orient][$size]['h'] - (float) $margin['top'] - (float) $margin['bottom'];
        $itemsPerRow   = (int) floor($contentWidth / $itemWidth);
        $rowsPerPage   = (int) floor($contentHeight / $itemHeight);

        return $itemsPerRow * $rowsPerPage;
    }
}
