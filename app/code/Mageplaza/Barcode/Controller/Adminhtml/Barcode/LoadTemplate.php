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
use Mageplaza\Barcode\Helper\Template as TemplateHelper;

/**
 * Class LoadTemplate
 * @package Mageplaza\Barcode\Controller\Adminhtml\Barcode
 */
class LoadTemplate extends AbstractPrint
{
    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $paper = $this->getRequest()->getParam('paper');
        $label = $this->getRequest()->getParam('label', 'standard');
        if ($paper === null) {
            return $this->errorMessage(self::MISSING_PARAMETERS);
        }

        $paperData    = $this->_paperTemplate->getTemplateBaseSpecs($paper);
        $labelBarcode = $this->_paperTemplate->getBarcodeBaseSpecs($paper, $label);

        try {
            $templateHtml = $this->_helperData->getDefaultTemplate($label, TemplateHelper::HTML, $paper);
            $templateCss  = $this->_helperData->getDefaultTemplate($label, TemplateHelper::CSS, $paper);

            return $this->_json->setData([
                'paper' => $paperData,
                'label' => [
                    'html'     => $templateHtml,
                    'css'      => $templateCss,
                    'template' => 'standard',
                    'width'    => $labelBarcode['width'],
                    'height'   => $labelBarcode['height'],
                    'size'     => $labelBarcode['size'],
                ],
            ]);
        } catch (Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }
}
