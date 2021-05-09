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

namespace Mageplaza\Barcode\Controller\Adminhtml\Barcode;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PreviewPaper
 * @package Mageplaza\Barcode\Controller\Adminhtml\System
 */
class PreviewPaper extends AbstractPrint
{
    /**
     * @var bool
     */
    private $configPreview = false;

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $params    = $this->getRequest()->getParams();
        $paperData = $this->processParameters($params);
        if ($this->hasError) {
            return $this->errorMessage($this->errorMessage);
        }

        $products    = [];
        $barcodeData = [];
        $labelData   = [];
        $maxItems    = $this->getPageMaxItem($paperData);

        if (!$this->configPreview) {
            $barcodeData = $this->getBarcodeData($params['type'], $params['paper'], $params['label']);
            $labelData   = $this->getLabelData($params['paper'], $params['label']);
            $products    = $this->_helperData->getPrintProducts($maxItems, $this->getStoreId());
        }

        try {
            $pdfString = $this->configPreview
                ? $this->previewConfigPaper($maxItems, $paperData)
                : $this->printBarcode(1, $products, $barcodeData, $paperData, $labelData);

            return $this->_json->setData(['data' => base64_encode($pdfString)]);
        } catch (Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }

    /**
     * @param array $p
     *
     * @return array|bool
     * @throws NoSuchEntityException
     */
    public function processParameters($p)
    {
        // Admin Config Preview
        if (isset($p['s'], $p['w'], $p['h'], $p['p'], $p['m'], $p['vs'], $p['hs'], $p['o'], $p['t'])) {
            $this->configPreview = true;
            $floatInput          = [
                'width'      => $p['w'],
                'height'     => $p['h'],
                'padding'    => $p['p'],
                'vertical'   => $p['vs'],
                'horizontal' => $p['hs'],
            ];

            $floatInput = $this->validateFloat($floatInput);
            if ($floatInput === false) {
                return [];
            }

            if (substr_count($p['m'], ';') + 1 !== 4) {
                return $this->raiseError(self::ERROR_PAPER_MARGIN);
            }

            $paperMargin = $this->_helperData->getPaperMargin($p['m'], $this->getStoreId(), true);
            $paperMargin = $this->validateFloat($paperMargin, null, 'Paper Margin');
            if ($paperMargin === false) {
                return [];
            }

            $paperMargin['bottom'] -= $floatInput['vertical'];
            if ($paperMargin['bottom'] < 0) {
                return $this->raiseError(self::ERROR_VERTICAL_SPACING);
            }

            $paperMargin['left'] -= $floatInput['horizontal'];
            if ($paperMargin['left'] < 0) {
                return $this->raiseError(self::ERROR_HORIZONTAL_SPACING);
            }

            return [
                'width'          => $floatInput['width'],
                'height'         => $floatInput['height'],
                'padding'        => $floatInput['padding'],
                'margin'         => $paperMargin,
                'vertical'       => $floatInput['vertical'],
                'horizontal'     => $floatInput['horizontal'],
                'vertical_pitch' => $floatInput['height'] + $floatInput['vertical'],
                'size'           => $p['s'],
                'orient'         => $p['o'],
                'template'       => $p['t'],
            ];
        }

        // Product Preview
        if (isset($p['paper'], $p['label'], $p['type'])) {
            return $this->getPaperData($p['paper']);
        }

        return $this->raiseError(self::MISSING_PARAMETERS);
    }
}
