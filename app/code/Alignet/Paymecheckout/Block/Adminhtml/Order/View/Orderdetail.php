<?php
namespace Alignet\Paymecheckout\Block\Adminhtml\Order\View;

class Orderdetail extends \Magento\Backend\Block\Template
{

	public function __construct(
		\Magento\Backend\Block\Template\Context $context
	)
	{
		parent::__construct($context);
	}

	public function getResponse()
	{
		$data = [];
		$data[0] = '';
		$data[1] = '';
        $orderId = $this->getRequest()->getParam('order_id');

        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
	    $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
	    $data[0] = $connection->fetchAll("SELECT * FROM payme_log Where id_order = $orderId");
 		$data[1] = $connection->fetchAll("SELECT * FROM payme_request Where purchaseOperationNumber = $orderId");



		return $data;
	}

}