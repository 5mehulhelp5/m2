<?php
namespace Meetanshi\Paymulti\Model\Express;

use Magento\Customer\Api\Data\CustomerInterface as CustomerDataObject;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\DataObject;
use Magento\Paypal\Model\Cart as PaypalCart;
use Magento\Paypal\Model\Config as PaypalConfig;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

class Checkout extends \Magento\Paypal\Model\Express\Checkout
{
    protected $_helper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Paypal\Model\Info $paypalInfo,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $coreUrl,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Checkout\Model\Type\OnepageFactory $onepageFactory,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
        \Magento\Paypal\Model\Api\Type\Factory $apiTypeFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        AccountManagement $accountManagement,
        OrderSender $orderSender,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Meetanshi\Paymulti\Helper\Data $helper,
        $params = []
    ) {
        
        $this->_helper = $helper;
        parent::__construct($logger, $customerUrl, $taxData, $checkoutData, $customerSession, $configCacheType, $localeResolver, $paypalInfo, $storeManager, $coreUrl, $cartFactory, $onepageFactory, $quoteManagement, $agreementFactory, $apiTypeFactory, $objectCopyService, $checkoutSession, $encryptor, $messageManager, $customerRepository, $accountManagement, $orderSender, $quoteRepository, $totalsCollector, $params);  
    }

    public function start($returnUrl, $cancelUrl, $button = null)
    {
        $this->_quote->collectTotals();

        if (!$this->_quote->getGrandTotal()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'PayPal can\'t process orders with a zero balance due. '
                    . 'To finish your purchase, please go through the standard checkout process.'
                )
            );
        }

        $this->_quote->reserveOrderId();
        $this->quoteRepository->save($this->_quote);
        // prepare API
        $solutionType = $this->_config->getMerchantCountry() == 'DE'
            ? \Magento\Paypal\Model\Config::EC_SOLUTION_TYPE_MARK
            : $this->_config->getValue('solutionType');

        
        if($this->_helper->getEnableModule()){
            $amount = $this->_helper->getConvertedBaseAmount($this->_quote);
            $currency_symbole = $this->_helper->getToCurrency();    
        }
        else{
            $amount = $this->_quote->getBaseGrandTotal();
            $currency_symbole = $this->_quote->getBaseCurrencyCode();    
        }
        
        $this->_getApi()->setAmount($amount)
            ->setCurrencyCode($currency_symbole)
            ->setInvNum($this->_quote->getReservedOrderId())
            ->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl)
            ->setSolutionType($solutionType)
            ->setPaymentAction($this->_config->getValue('paymentAction'));
        if ($this->_giropayUrls) {
            list($successUrl, $cancelUrl, $pendingUrl) = $this->_giropayUrls;
            $this->_getApi()->addData(
                [
                    'giropay_cancel_url' => $cancelUrl,
                    'giropay_success_url' => $successUrl,
                    'giropay_bank_txn_pending_url' => $pendingUrl,
                ]
            );
        }

        if ($this->_isBml) {
            $this->_getApi()->setFundingSource('BML');
        }

        $this->_setBillingAgreementRequest();

        if ($this->_config->getValue('requireBillingAddress') == PaypalConfig::REQUIRE_BILLING_ADDRESS_ALL) {
            $this->_getApi()->setRequireBillingAddress(1);
        }

        // suppress or export shipping address
        $address = null;
        if ($this->_quote->getIsVirtual()) {
            if ($this->_config->getValue('requireBillingAddress')
                == PaypalConfig::REQUIRE_BILLING_ADDRESS_VIRTUAL
            ) {
                $this->_getApi()->setRequireBillingAddress(1);
            }
            $this->_getApi()->setSuppressShipping(true);
        } else {
            $this->_getApi()->setBillingAddress($this->_quote->getBillingAddress());

            $address = $this->_quote->getShippingAddress();
            $isOverridden = 0;
            if (true === $address->validate()) {
                $isOverridden = 1;
                $this->_getApi()->setAddress($address);
            }
            $this->_quote->getPayment()->setAdditionalInformation(
                self::PAYMENT_INFO_TRANSPORT_SHIPPING_OVERRIDDEN,
                $isOverridden
            );
            $this->_quote->getPayment()->save();
        }

        /** @var $cart \Magento\Payment\Model\Cart */
        $cart = $this->_cartFactory->create(['salesModel' => $this->_quote]);

        $this->_getApi()->setPaypalCart($cart);

        if (!$this->_taxData->getConfig()->priceIncludesTax()) {
            $this->setShippingOptions($cart, $address);
        }

        $this->_config->exportExpressCheckoutStyleSettings($this->_getApi());

        /* Temporary solution. @TODO: do not pass quote into Nvp model */
        $this->_getApi()->setQuote($this->_quote);
        $this->_getApi()->callSetExpressCheckout();

        $token = $this->_getApi()->getToken();

        $this->_setRedirectUrl($button, $token);

        $payment = $this->_quote->getPayment();
        $payment->unsAdditionalInformation(self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
        // Set flag that we came from Express Checkout button
        if (!empty($button)) {
            $payment->setAdditionalInformation(self::PAYMENT_INFO_BUTTON, 1);
        } elseif ($payment->hasAdditionalInformation(self::PAYMENT_INFO_BUTTON)) {
            $payment->unsAdditionalInformation(self::PAYMENT_INFO_BUTTON);
        }
        $payment->save();

        return $token;
    }

    private function setShippingOptions(PaypalCart $cart, Address $address = null)
    {
        // for included tax always disable line items (related to paypal amount rounding problem)
        $this->_getApi()->setIsLineItemsEnabled($this->_config->getValue(PaypalConfig::TRANSFER_CART_LINE_ITEMS));

        // add shipping options if needed and line items are available
        $cartItems = $cart->getAllItems();
        if ($this->_config->getValue(PaypalConfig::TRANSFER_CART_LINE_ITEMS)
            && $this->_config->getValue(PaypalConfig::TRANSFER_SHIPPING_OPTIONS)
            && !empty($cartItems)
        ) {
            if (!$this->_quote->getIsVirtual()) {
                $options = $this->_prepareShippingOptions($address, true);
                if ($options) {
                    $this->_getApi()->setShippingOptionsCallbackUrl(
                        $this->_coreUrl->getUrl(
                            '*/*/shippingOptionsCallback',
                            ['quote_id' => $this->_quote->getId()]
                        )
                    )->setShippingOptions($options);
                }
            }
        }
    }

    
}
