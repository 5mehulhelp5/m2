<?php
namespace Alignet\Paymecheckout\Controller\Classic;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
class Response extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface {
 
	 /**
	 * @var \Alignet\Paymecheckout\Model\Session
	 */
	protected $session;

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;


	/**
	 * @var \Magento\Sales\Model\OrderFactory
	 */
	protected $_orderFactory;


	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Alignet\Paymecheckout\Model\Session $session
	 */
	function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Alignet\Paymecheckout\Model\Session $session,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	) {
		parent::__construct($context);
		$this->session = $session;
		$this->resultPageFactory = $resultPageFactory;
		$this->_orderFactory = $orderFactory;
		$this->orderRepository = $orderRepository;
	}

	/**
	 * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
	 */
	function execute(){
		$resultPage = $this->resultPageFactory->create();
		$response = $this->getRequest()->getPostValue();
		$this->session->setPostdata($response);
		$authorizationResult = trim($response['authorizationResult']) == "" ? "-" : $response['authorizationResult'];
		$response['paymentReferenceCode'] = trim(isset($response['paymentReferenceCode'])) == "" ? "-" : $response['paymentReferenceCode'];
		$response['purchaseVerification'] =trim(isset($response['purchaseVerification'])) == "" ? "-" : $response['purchaseVerification'];
		$response['purchaseOperationNumber'] = str_pad($response['purchaseOperationNumber'], 8, "0", STR_PAD_LEFT);
		$response['plan'] = trim(isset($response['plan'])) == "" ? "-" : $response['plan'];
		$response['cuota'] =  trim(isset($response['cuota'])) == "" ? "-" : $response['cuota'];
		$response['montoAproxCuota'] =  trim(isset($response['montoAproxCuota'])) == "" ? "-" : $response['montoAproxCuota'];
		$response['resultadoOperacion'] =  trim(isset($response['resultadoOperacion'])) == "" ? "-" : $response['resultadoOperacion'];
		$response['paymethod'] =  trim(isset($response['paymethod'])) == "" ? "-" : $response['paymethod'];
		$response['fechaHora'] =  trim(isset($response['fechaHora'])) == "" ? "-" : $response['fechaHora'];
		$response['numeroCip'] =  trim(isset($response['numeroCip'])) == "" ? "-" : $response['numeroCip'];
		$response['brand'] =  trim(isset($response['brand'])) == "" ? "-" : $response['brand'];
		$orderId = (int) substr($response['purchaseOperationNumber'],4,6);
		if ($orderId) {
		   $order = $this->orderRepository->get($orderId);
		}
		else {
		   echo  $response['answerMessage'];
		   die();
		}
		$iso_code = $response['purchaseCurrencyCode'] ;
		switch ($iso_code) {
			case '840':
				$response['purchaseCurrencyCode'] = 'USD ';
				break;
			case '604':
				$response['purchaseCurrencyCode'] = 'S/ ';
				break;
			 case '068':
				$response['purchaseCurrencyCode'] = ' BS ';
				break;
			 case '188':
				$response['purchaseCurrencyCode'] = 'CRC ';
				break;
			default:
				$response['purchaseCurrencyCode'] = 'USD';
				break;
		}
		$response['orden'] =  $orderId;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('payme_log');
		$sqlDelete = "Delete FROM " . $tableName." Where purchaseOperationNumber = $orderId";
		$connection->query($sqlDelete);
		$sql = "Insert Into " . $tableName . " 
			( 
				id_order,
				authorizationResult,
				authorizationCode,
				errorCode,
				errorMessage,
				bin,
				brand,
				paymentReferenceCode,
				purchaseOperationNumber,
				purchaseAmount,
				purchaseCurrencyCode,
				purchaseVerification,
				plan,
				cuota,
				montoAproxCuota,
				resultadoOperacion,
				paymethod,
				fechaHora,
				reserved1,
				reserved2,
				reserved3,
				reserved4,
				reserved5,
				reserved6,
				reserved7,
				reserved8,
				reserved9,
				reserved10,
				numeroCip
			)
			Values (				
			   '".$orderId."',
			   '".$response['authorizationResult']."',
			   '".$response['authorizationCode']."',
			   '".$response['errorCode']."',
			   '".$response['errorMessage']."',
			   '".$response['bin']."',
			   '".$response['brand']."',
			   '".$response['paymentReferenceCode']."',
			   '".$response['purchaseOperationNumber']."',
			   '".$response['purchaseAmount']."',
			   '".$response['purchaseCurrencyCode']."',
			   '".$response['purchaseVerification']."',
			   '".$response['plan']."',
			   '".$response['cuota']."',
			   '".$response['montoAproxCuota']."',
			   '".$response['resultadoOperacion']."',
			   '".$response['paymethod']."',
			   '".$response['fechaHora']."',
			   '".$response['reserved1']."',
			   '".$response['reserved2']."',
			   '".$response['reserved3']."',
			   '".$response['reserved4']."',
			   '".$response['reserved5']."',
			   '".$response['reserved6']."',
			   '".$response['reserved7']."',
			   '".$response['reserved8']."',
			   '".$response['reserved9']."',
			   '".$response['reserved10']."',
			   '".$response['numeroCip']."'
			)";
		$connection->query($sql);
		if ($authorizationResult == '00') {
			$fechaHora = $response['txDateTime'];
			$response['msgNumeroOP'] = 'Su transacción con número de pedido '.$response['purchaseOperationNumber'].' fue autorizada con éxito.';
			$response['msgFecha'] = 'Este pedido fue generado el ' .$fechaHora .', en breve recibirá un correo a '.$response['shippingEmail'].' con la confirmación del pago el cual debe imprimir y/o guardar ';
			$response['responseMSG'] = 'Transacción Autorizada';
			$response['titleColor'] = 'success';

			$order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true)->save();
			$order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING, true)->save();
			$order->addStatusToHistory($order->getStatus(), 'El pedido ha sido procesado Correctamente');
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$objectManager->create('Magento\Sales\Model\OrderNotifier')->notify($order);
			$order->save();
		}
		elseif ($authorizationResult == '01') {
			$fechaHora = date("d/m/Y H:i:s");
			$response['msgNumeroOP'] = 'Su transacción con número de pedido '.$response['purchaseOperationNumber'].' fue Denegada.  Tener presente que esta operación NO HA GENERADO NINGUN COBRO en su tarjeta.';
			$response['responseMSG'] = 'Transacción Denegada';
			$response['titleColor'] = 'danger';
			$order->setState(\Magento\Sales\Model\Order::STATUS_REJECTED, true)->save();
			$order->setStatus(\Magento\Sales\Model\Order::STATUS_REJECTED, true)->save();
			$order->addStatusToHistory($order->getStatus(), 'El pedido ha sido procesado Correctamente');
			$order->save();
		}
		elseif ($authorizationResult == '05') {
			$response['msgFecha'] ='-';
			$response['answerMessage'] ='Transacción Cancelada';
			$response['msgNumeroOP'] = 'Su transacción con número de pedido '.$response['purchaseOperationNumber'].' fue Cancelada. Tener presente que esta operación NO HA GENERADO NINGUN COBRO en su tarjeta.';
			$response['responseMSG'] = 'Transacción Cancelada';
			$response['titleColor'] = 'danger';
			$order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();
			$order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();
			$order->addStatusToHistory($order->getStatus(), 'El pedido ha sido Cancelado ');
			$order->save();
		}
		elseif ($authorizationResult == '03') {
			if ($response['brand'] == 6 || $response['brand'] == 25) {
				$response['brand'] = "PAGO EFECTIVO";
			}
			elseif ($response['brand'] == 7 || $response['brand']== 34) {
				$response['brand'] = "SAFETYPAY";
			}
			else {
				$response['brand'] = "-";
			}
			$response['msgFecha'] = '-';
			$response['answerMessage'] ='Transacción Pendiente';
			$response['msgNumeroOP'] = 'Su transacción '.$response['purchaseOperationNumber'].' se encuentra pendiente de pago. Por favor acérquese a la agencia bancaria más cercana para realizar el pago con el siguiente código: <p class="pagoefectivo-cip">CIP: <b> '.$response['numeroCip'].'</b></p>';
			$response['responseMSG'] = 'Transacción Pendiente';
			$response['titleColor'] = 'success';
			$order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, true)->save();
			$order->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, true)->save();
			$order->addStatusToHistory($order->getStatus(), 'El pedido ha sido procesado Correctamente');
			$order->save();
		}
		else {
			$response['msgFecha'] = '-';
			$response['responseMSG'] = 'Incompleta';
			$response['titleColor'] = 'danger';
			$response['msgNumeroOP'] = 'Su transacción con número de pedido '.$response['purchaseOperationNumber'].' fue Incompleta. Tener presente que esta operación NO HA GENERADO NINGUN COBRO en su tarjeta.';
			$order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();
			$order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true)->save();
			$order->addStatusToHistory($order->getStatus(), 'El pedido ha sido procesado Correctamente');
			$order->save();
		}
		$resultPage->getLayout()->getBlock('paymecheckout.classic.response')->setPostdata($response);
		return $resultPage;
	}

	function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
	{
		return null;
	}

	function validateForCsrf(RequestInterface $request): ?bool
	{
		return true;
	}
}