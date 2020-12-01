<?php
namespace Meetanshi\Paymulti\Model\Api;

use Magento\Payment\Model\Cart;
use Magento\Payment\Model\Method\Logger;

class Nvp extends \Magento\Paypal\Model\Api\Nvp
{
    protected $_helper;
    protected $_request;
    protected $_rounder;

    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Psr\Log\LoggerInterface $logger,
        Logger $customLogger,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Paypal\Model\Api\ProcessableExceptionFactory $processableExceptionFactory,
        \Magento\Framework\Exception\LocalizedExceptionFactory $frameworkExceptionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Meetanshi\Paymulti\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Meetanshi\Paymulti\Helper\Rounding $rounder,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_rounder = $rounder;
        parent::__construct($customerAddress, $logger, $customLogger, $localeResolver, $regionFactory, $countryFactory, $processableExceptionFactory, $frameworkExceptionFactory, $curlFactory, $data);
    }

    protected function _exportLineItemsCustom(array &$request, $i = 0)
    {
        if (!$this->_cart) {
            return;
        }

        $extrapricearray = array();
        // always add cart totals, even if line items are not requested
        if ($this->_lineItemTotalExportMap) {
            foreach ($this->_cart->getAmounts() as $key => $total) {
                if (isset($this->_lineItemTotalExportMap[$key])) {
                    
                    if($this->_helper->getEnableModule()){
                        $total = $this->_helper->getConvertedAmount($total);    
                    }
                    
                    $privateKey = $this->_lineItemTotalExportMap[$key];
                    $request[$privateKey] = $this->formatPrice($total);

                    if($key != 'subtotal'){
                        $this->_rounder->addExtraPrice($key,$total);
                    }
                }
            }
        }
        
        // add cart line items
        $items = $this->_cart->getAllItems();
        if (empty($items) || !$this->getIsLineItemsEnabled()) {
            return;
        }

        $itempricearray = array();
        $result = null;
        foreach ($items as $item) {
            foreach ($this->_lineItemExportItemsFormat as $publicKey => $privateFormat) {
                $result = true;
                $value = $item->getDataUsingMethod($publicKey);
                if($publicKey == 'amount' && $this->_helper->getEnableModule()){
                    $value = $this->_helper->getConvertedAmount($value);
                }
                
                if($publicKey == 'qty'){
                    $this->_rounder->addItemPrice($i,'qty',$value);
                }
                if($publicKey == 'amount'){
                    $this->_rounder->addItemPrice($i,'amount',number_format($value,2));
                }
                
                $request[sprintf($privateFormat, $i)] = $this->formatValue($value, $publicKey);
            }
            $i++;
        }
        
        //$result = $this->_rounder->convertRequest($request);
        return $result;
    }

    private function formatValue($value, $publicKey)
    {
        if (!empty($this->_lineItemExportItemsFilters[$publicKey])) {
            $callback = $this->_lineItemExportItemsFilters[$publicKey];
            $value = method_exists($this, $callback) ? $this->{$callback}($value) : $callback($value);
        }

        if (is_float($value)) {
            $value = $this->formatPrice($value);
        }

        return $value;
    }

    protected function _exportShippingOptions(array &$request, $i = 0)
    {
        $options = $this->getShippingOptions();
        if (empty($options)) {
            return false;
        }
        foreach ($options as $option) {
            foreach ($this->_shippingOptionsExportItemsFormat as $publicKey => $privateFormat) {
                $value = $option->getDataUsingMethod($publicKey);
                if (is_float($value)) {
                    if($this->_helper->getEnableModule()){
                        $value = $this->_helper->getConvertedAmount($value);    
                    }
                    $value = $this->formatPrice($value);
                }
                if (is_bool($value)) {
                    $value = $this->_filterBool($value);
                }
                $request[sprintf($privateFormat, $i)] = $value;
            }
            $i++;
        }
        
        return true;
    }

    protected function _exportLineItems(array &$request, $i = 0)
    {
        if (!$this->_cart) {
            return;
        }
        $this->_cart->setTransferDiscountAsItem();
        return $this->_exportLineItemsCustom($request, $i);
    }
    
    public function callSetExpressCheckout()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);
        $this->_exportLineItems($request);
       
        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 0;
        } elseif ($options && count($options) <= 10) {
            // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6;
            // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00;
            // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }
        //echo "<Pre>";
        //print_r($request); exit;
        $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
        $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);
    }

    public function callDoExpressCheckoutPayment()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
        $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
        $this->_exportLineItems($request);

        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 0;
        }
       
        $response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
        $this->_importFromResponse($this->_createBillingAgreementResponse, $response);
    }

    public function call($methodName, array $request)
    {
        $request = $this->_addMethodToRequest($methodName, $request);
        $eachCallRequest = $this->_prepareEachCallRequest($methodName);
        if ($this->getUseCertAuthentication()) {
            $key = array_search('SIGNATURE', $eachCallRequest);
            if ($key) {
                unset($eachCallRequest[$key]);
            }
        }
        $request = $this->_exportToRequest($eachCallRequest, $request);
        $debugData = ['url' => $this->getApiEndpoint(), $methodName => $request];
        
        if(isset($request['METHOD']) && ($request['METHOD'] == self::DO_CAPTURE || $request['METHOD'] == self::REFUND_TRANSACTION)){
            $orderId = $this->_request->getParam('order_id');
            if($orderId){
                $paymentCurrency = $this->_helper->getPaymentCurrency($orderId);
                $request['AMT'] = number_format($this->_helper->convert($request['AMT'],null,$paymentCurrency),2);
                $request['CURRENCYCODE'] = $paymentCurrency;    
            }
        }

        try {
            $http = $this->_curlFactory->create();
            $config = ['timeout' => 60, 'verifypeer' => $this->_config->getValue('verifyPeer')];
            if ($this->getUseProxy()) {
                $config['proxy'] = $this->getProxyHost() . ':' . $this->getProxyPort();
            }
            if ($this->getUseCertAuthentication()) {
                $config['ssl_cert'] = $this->getApiCertificate();
            }
            $http->setConfig($config);
            $http->write(
                \Zend_Http_Client::POST,
                $this->getApiEndpoint(),
                '1.1',
                $this->_headers,
                $this->_buildQuery($request)
            );
            $response = $http->read();
        } catch (\Exception $e) {
            $debugData['http_error'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $this->_debug($debugData);
            throw $e;
        }
        // echo '<pre>';print_r($request);exit;
        $response = preg_split('/^\r?$/m', $response, 2);
        $response = trim($response[1]);
        $response = $this->_deformatNVP($response);

        $debugData['response'] = $response;
        $this->_debug($debugData);

        $response = $this->_postProcessResponse($response);

        // handle transport error
        if ($http->getErrno()) {
            $this->_logger->critical(
                new \Exception(
                    sprintf('PayPal NVP CURL connection error #%s: %s', $http->getErrno(), $http->getError())
                )
            );
            $http->close();

            throw new \Magento\Framework\Exception\LocalizedException(
                __('Payment Gateway is unreachable at the moment. Please use another payment option.')
            );
        }

        // cUrl resource must be closed after checking it for errors
        $http->close();

        if (!$this->_validateResponse($methodName, $response)) {
            $this->_logger->critical(new \Exception(__('PayPal response hasn\'t required fields.')));
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while processing your order.')
            );
        }

        $this->_callErrors = [];
        if ($this->_isCallSuccessful($response)) {
            if ($this->_rawResponseNeeded) {
                $this->setRawSuccessResponseData($response);
            }
            return $response;
        }
        $this->_handleCallErrors($response);
        return $response;
    }
}
