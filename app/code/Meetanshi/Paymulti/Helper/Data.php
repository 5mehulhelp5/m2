<?php

namespace Meetanshi\Paymulti\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;

class Data extends AbstractHelper
{
    const MEETANSHI_MODULE_ENABLE = 'payment/multicurrency/active';
    const MEETANSHI_EXTRA_CURRENCY = 'payment/multicurrency/extra_currency';
    const MEETANSHI_CHECKOUT_CURRENCY = 'payment/multicurrency/to_currency';

    protected $storeManager;

    protected $order;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        OrderInterface $order
    )
    {
        $this->storeManager = $storeManager;
        $this->order = $order;
        parent::__construct($context);
    }

    public function getEnableModule()
    {

        return $this->scopeConfig->getValue(self::MEETANSHI_MODULE_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCheckoutDefaultCurrency()
    {

        $page = $this->scopeConfig->getValue(self::MEETANSHI_CHECKOUT_CURRENCY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return ($page) ? $page : 'USD';
    }

    public static function getSupportedCurrency()
    {

        return array('AUD', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN',
            'NOK', 'NZD', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'USD', 'TWD', 'THB', 'INR');
    }

    public static function shouldConvert()
    {

        return !self::isActive();
    }

    public function isActive()
    {
        $state = $this->getEnableModule();
        if (!$state) {
            return;
        }
        return $state;
    }

    public function getToCurrency()
    {

        $to = $this->getCheckoutDefaultCurrency();
        if (!$to) {
            $to = 'USD';
        }

        /*$current_currency = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $getSupportedCurrency = $this->getSupportedCurrency();
        if(in_array($current_currency, $getSupportedCurrency)){  
            $to = $current_currency;        
        }*/
        return $to;
    }

    public function convert($amountValue, $currencyCodeFrom = null, $currencyCodeTo = null)
    {

        return $this->storeManager->getStore()->getBaseCurrency()->convert($amountValue, $currencyCodeTo);
    }

    public function getConvertedBaseAmount($quote)
    {

        $toCur = $this->getToCurrency();
        $current_currency = $this->storeManager->getStore()->getCurrentCurrencyCode();
        if ($toCur == $current_currency) {
            return $quote->getGrandTotal();
        } else {
            return $this->getConvertedAmount($quote->getBaseGrandTotal());
        }

    }

    public function getConvertedAmount($value)
    {

        $baseCode = $this->storeManager->getStore()->getBaseCurrencyCode();
        $toCur = $this->getToCurrency();
        $roundedvalue = $this->convert($value, $baseCode, $toCur);
        return $roundedvalue;
    }

    public function getCurrencyArray()
    {

        return array($this->storeManager->getStore()->getBaseCurrencyCode());
    }

    public function getPaymentCurrency($orderID)
    {

        $order = $this->order->load($orderID);
        if ($order) {
            $payment = $order->getPayment();
            return $payment->getAdditionalInformation('payment_currency');
        }
        return $this->getToCurrency();
    }

    public function getConfig($identifier)
    {

        return $this->scopeConfig->getValue(
            $identifier,
            ScopeInterface::SCOPE_STORE
        );
    }
}