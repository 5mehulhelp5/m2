<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Controller\Payment;

class Repeat extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Alignet\Paymecheckout\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * @var \Alignet\Paymecheckout\Model\Session
     */
    protected $session;

    function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Alignet\Paymecheckout\Helper\Payment $paymentHelper,
        \Alignet\Paymecheckout\Model\Session $session
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->paymentHelper = $paymentHelper;
        $this->session = $session;
    }

    function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect;
    }
}
