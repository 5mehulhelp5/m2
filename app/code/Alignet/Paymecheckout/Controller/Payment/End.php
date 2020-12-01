<?php
/**
 * @copyright Copyright (c) 2019 Alignet 
 */

namespace Alignet\Paymecheckout\Controller\Payment;

use Magento\Framework\Exception\LocalizedException;

class End extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session\SuccessValidator
     */
    protected $successValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Alignet\Payme\Model\Session
     */
    protected $session;

    /**
     * @var \Alignet\Payme\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Alignet\Payme\Model\Order
     */
    protected $orderHelper;

    /**
     * @var \Alignet\Paymecheckout\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session\SuccessValidator $successValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Alignet\Paymecheckout\Model\Session $session
     * @param \Alignet\Paymecheckout\Model\ClientFactory $clientFactory
     * @param \Alignet\Paymecheckout\Model\Order $orderHelper
     * @param \Alignet\Paymecheckout\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session\SuccessValidator $successValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Alignet\Paymecheckout\Model\Session $session,
        \Alignet\Paymecheckout\Model\ClientFactory $clientFactory,
        \Alignet\Paymecheckout\Model\Order $orderHelper,
        \Alignet\Paymecheckout\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->successValidator = $successValidator;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->clientFactory = $clientFactory;
        $this->orderHelper = $orderHelper;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /**
         * @var $clientOrderHelper \Alignet\Paymecheckout\Model\Client\OrderInterface
         */



        // $resultRedirect = $this->resultRedirectFactory->create();
        // $redirectUrl = '/';
        // try {
        //     if ($this->successValidator->isValid()) {
        //         $redirectUrl = 'payme/payment/error';
        //         $this->session->setLastOrderId(null);
        //         $clientOrderHelper = $this->getClientOrderHelper();
        //         if ($this->orderHelper->paymentSuccessCheck() && $clientOrderHelper->paymentSuccessCheck()) {
        //             $redirectUrl = 'checkout/onepage/success';
        //         }

        //     } else {
        //         if ($this->session->getLastOrderId()) {
        //             $redirectUrl = 'payme/payment/repeat_error';
        //             $clientOrderHelper = $this->getClientOrderHelper();
        //             if ($this->orderHelper->paymentSuccessCheck() && $clientOrderHelper->paymentSuccessCheck()) {
        //                 $redirectUrl = 'payme/payment/repeat_success';
        //             }
        //         }
        //     }
        // } catch (LocalizedException $e) {
        //     $this->logger->critical($e);
        // }
        // $resultRedirect->setPath($redirectUrl);


        $resultPage = $this->resultPageFactory->create();
        $data = ['message' => 'Hello world!'];
        $this->session->setPostdata($data);
        $postdata = $this->session->getPostdata();
        $resultPage->getLayout()->getBlock('paymecheckout.payment.end')->setPostdata($data);
        return $resultPage;
        
        // return $resultRedirect;
    }

    /**
     * @return \Alignet\Payme\Model\Client\OrderInterface
     */
    protected function getClientOrderHelper()
    {
        return $this->clientFactory->create()->getOrderHelper();
    }
}