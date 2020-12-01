<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Block\Payment;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var \Alignet\Paymecheckout\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Alignet\Paymecheckout\Model\ClientFactory
     */
    protected $clientFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Alignet\Paymecheckout\Model\ResourceModel\Transaction $transactionResource
     * @param \Alignet\Paymecheckout\Model\ClientFactory $clientFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Alignet\Paymecheckout\Model\ResourceModel\Transaction $transactionResource,
        \Alignet\Paymecheckout\Model\ClientFactory $clientFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->transactionResource = $transactionResource;
        $this->clientFactory = $clientFactory;
    }

    protected function _prepareLayout()
    {
        $this->addChild('buttons', Info\Buttons::class);
        parent::_prepareLayout();
    }

    protected function _prepareSpecificInformation($transport = null)
    {
        /**
         * @var $client \Alignet\Paymecheckout\Model\Client
         */
        $transport = parent::_prepareSpecificInformation($transport);
        $orderId = $this->getInfo()->getParentId();
        $status = $this->transactionResource->getLastStatusByOrderId($orderId);
        $client = $this->clientFactory->create();
        $statusDescription = $client->getOrderHelper()->getStatusDescription($status);
        $transport->setData((string) __('Status'), $statusDescription);
        return $transport;
    }
}
