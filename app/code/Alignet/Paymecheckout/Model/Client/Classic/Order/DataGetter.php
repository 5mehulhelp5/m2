<?php
namespace Alignet\Paymecheckout\Model\Client\Classic\Order;
use Alignet\Paymecheckout\Plugin\Magento\Framework\Session\SidResolver as PSidResolver;
# 2020-12-09
final class DataGetter {
	 /**
	 * @var \Alignet\Payme\Model\Order\ExtOrderId
	 */
	protected $extOrderIdHelper;

	/**
	 * @var \Alignet\Payme\Model\Client\Classic\Config
	 */
	protected $configHelper;

	/**
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	protected $dateTime;

	/**
	 * @var \Alignet\Payme\Model\Session
	 */
	protected $session;



	/**
	 * @var \Alignet\Payme\Model\Session
	 */
	protected $idEntCommerce;


	/**
	 * @var \Alignet\Payme\Model\Session
	 */
	protected $keywallet;



	/**
	 * @var \Alignet\Payme\Model\Session
	 */
	protected $idCommerce;



	/**
	 * @var \Alignet\Payme\Model\Session
	 */
	protected $key;


	/**
	 * @var \Alignet\Payme\Model\Session
	 */
	protected $wsdl;

	protected $wsdomain;

	protected $modalVPOS2;

	protected $tipoModal;

	protected $currency_iso;


	/**
	 * @param \Alignet\Paymecheckout\Model\Order\ExtOrderId $extOrderIdHelper
	 * @param \Alignet\Paymecheckout\Model\Client\Classic\Config $configHelper
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
	 * @param \Alignet\Paymecheckout\Model\Session $session
	 */
	function __construct(
		\Alignet\Paymecheckout\Model\Order\ExtOrderId $extOrderIdHelper,
		\Alignet\Paymecheckout\Model\Client\Classic\Config $configHelper,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Alignet\Paymecheckout\Model\Session $session
	) {
		$this->extOrderIdHelper = $extOrderIdHelper;
		$this->configHelper = $configHelper;
		$this->dateTime = $dateTime;
		$this->session = $session;
	}

	/**
	 * @used-by \Alignet\Paymecheckout\Model\Client\Classic\Order::getDataForOrderCreate()
	 * @param \Magento\Sales\Model\Order $order
	 * @return array
	 */
	function getBasicData(\Magento\Sales\Model\Order $order) {
		$oid =(int)$order->getId(); /** @var int $oid */
		$billingAddress = $order->getBillingAddress();
		$billingAddresssArray =$billingAddress->getData();
		$shippingAddress = $order->getShippingAddress();
		$shippingAddressArray =$shippingAddress->getData();

		$taxReturnBase = number_format(($order->getGrandTotal() - $order->getTaxAmount()),2,'.','');

		if($order->getTaxAmount() == 0) $taxReturnBase = 0;
		if ($order->getOrderCurrencyCode() == 'USD') {
			$this->idEntCommerce = $this->configHelper->getConfig('idEntCommerce_usd');
			$this->keywallet = $this->configHelper->getConfig('keywallet_usd');
			$this->acquirerId = $this->configHelper->getConfig('acquirerId_usd');
			$this->idCommerce = $this->configHelper->getConfig('idCommerce_usd');
			$this->key = $this->configHelper->getConfig('key_usd');
			// $this->currency_iso = 840;
		}
		else
		{
			$this->idEntCommerce = $this->configHelper->getConfig('idEntCommerce');
			$this->keywallet = $this->configHelper->getConfig('keywallet');
			$this->acquirerId = $this->configHelper->getConfig('acquirerId');
			$this->idCommerce = $this->configHelper->getConfig('idCommerce');
			$this->key = $this->configHelper->getConfig('key');
			// $this->currency_iso = 604;
		}

		$this->currency_iso = $this->setCurrencyIso($order->getOrderCurrencyCode());        // $incrementId = str_replace('.','',number_format($order->getGrandTotal(),2,'.',''));



		$long = ($this->acquirerId == 144 || $this->acquirerId == 29) ? 6 :6;

		$comerce = [
			'userCommerce' =>(string)$order->getCustomerId(),
			'billingEmail'=>$billingAddresssArray['email'],
			'billingFirstName'=>$shippingAddressArray['firstname'],
			'billingLastName'=>$shippingAddressArray['lastname'],
			'billingEmail'=> $billingAddresssArray['email'],
			'reserved1'=>'',
			'reserved2'=>'',
			'reserved3'=>'',
			'currency' =>$this->currency_iso
		];




		$purchaseAmountVar =str_replace('.','',number_format($order->getGrandTotal(),2,'.',''));

		$data = [
			'acquirerId' => $this->acquirerId,
			'idCommerce' =>  $this->idCommerce,
			'purchaseOperationNumber' => $oid,
			'purchaseAmount' =>  $purchaseAmountVar,
			'purchaseCurrencyCode' =>   $this->currency_iso,
			'language' => 'ES',
			'billingFirstName' =>$billingAddresssArray['firstname'],
			'billingLastName' =>$billingAddresssArray['lastname'],
			'billingEmail' => $billingAddresssArray['email'],
			'billingAddress' => $billingAddresssArray['street'],
			'billingZIP' => $billingAddresssArray['postcode'] ,
			'billingCity' =>$billingAddresssArray['city'],
			'billingState' => ($billingAddresssArray['region']) ? $billingAddresssArray['region'] : '-',
			'billingCountry' => ($order->getBillingAddress()->getCountryId()) ? $order->getBillingAddress()->getCountryId() : '-',
			'billingPhone' => $billingAddresssArray['telephone'],
			'shippingFirstName' => $shippingAddressArray['firstname'],
			'shippingLastName' => $shippingAddressArray['lastname'],
			'shippingEmail' =>$shippingAddressArray['email'],
			'shippingAddress' => $shippingAddressArray['street'],
			'shippingZIP' => $billingAddresssArray['postcode'],
			'shippingCity' =>$shippingAddressArray['city'],
			'shippingState' =>($shippingAddressArray['region']) ? $shippingAddressArray['region'] : '-',
			'shippingCountry' => ($order->getShippingAddress()->getCountryId()) ? $order->getShippingAddress()->getCountryId() : '-',
			'shippingPhone' =>$shippingAddressArray['telephone'],
			'userCommerce' =>  (string)$order->getCustomerId(),
			'userCodePayme' => $this->userCodePayme($comerce),
			'descriptionProducts' => 'Productos varios',
			'programmingLanguage' => 'ALG-MG-v3.0.3',
			# 2020-12-09 Dmitry Fedyuk https://www.upwork.com/fl/mage2pro
			PSidResolver::P_SESSION => session_id(),
			'reserved2' => '',
			'reserved3' => '',
			'reserved4' => '',
			'reserved5' => '',
			'reserved6' => '',
			'reserved7' => '',
			'reserved8' => '',
			'reserved9' => '',
			'reserved10' => '',
			'purchaseVerification' => $this->purchaseVerification($oid, $purchaseAmountVar, $this->currency_iso),

		];

		return $data;
	}

	function setCurrencyIso($code){
		$iso_code = '' ;
		switch ($code) {
			case 'USD':
				$iso_code = '840';
				break;
			case 'PEN':
				$iso_code = '604';
				break;
			 case 'BOB':
				$iso_code = '068';
				break;
			 case 'CRC':
				$iso_code = '188';
				break;
			default:
				$iso_code = '840';
				break;
		}

		return $iso_code;
	}

  function purchaseVerification($purchOperNum, $purchAmo, $purchCurrCod, $authRes = null)
	{
		$concatPurchase = $this->acquirerId.$this->idCommerce.$purchOperNum.$purchAmo.$purchCurrCod.$authRes.$this->key;

		return (phpversion() >= 5.3) ? openssl_digest($concatPurchase, 'sha512') : hash('sha512', $concatPurchase);
	}

	/**
	 * @return string
	 */
	function getMerchantId()
	{
		return $this->configHelper->getConfig('acquirerId');
	}

	/**
	 * @return string
	 */
	function getAccountId()
	{
		return $this->configHelper->getConfig('idCommerce');
	}

	/**
	 * @return string
	 */
	function getTestMode()
	{
		return $this->configHelper->getConfig('test');
	}

	/**
	 * @return string
	 */
	function getClientIp()
	{
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * @return int
	 */
	function getTs()
	{
		return $this->dateTime->timestamp();
	}

	/**
	 * @param array $data
	 * @return string
	 */
	function getSigForOrderCreate(array $data = [])
	{
		//Signature Format
		//“ApiKey~merchantId~referenceCode~amount~currency”.

		return md5(
			$this->configHelper->getConfig('keywallet')
		);
	}

	/**
	 * @param array $data
	 * @return string
	 */
	function getSigForOrderRetrieve(array $data = [])
	{
		return md5(
			$this->configHelper->getConfig('keywallet')
		);
	}

	/**
	 * @used-by getBasicData()
	 * @param array(string => mixed) $d
	 * @return string
	 * @throws \SoapFault
	 */
	private function userCodePayme(array $d) {
		$r = ''; /** @var string $r */
		$customerId = dfa($d, 'userCommerce'); /** @var int|null $customerId */
		$concatRegister = $this->idEntCommerce . $customerId . $d['billingEmail'] . $this->keywallet;
		$registerVerification = openssl_digest($concatRegister, 'sha512');
		if ($customerId) {
			$paramsWallet = [
				'idEntCommerce' => (string)$this->idEntCommerce,
				'codCardHolderCommerce' => (string)$customerId,
				'names' => $d['billingFirstName'],
				'lastNames' => $d['billingLastName'],
				'mail' => $d['billingEmail'] ,
				'reserved1' => $d['reserved1'],
				'reserved2' => $d['reserved2'],
				'reserved3' => $d['reserved3'],
				'registerVerification'=>$registerVerification
			];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('payme_usercode');
			$sql = "select * from $tableName where user_code = $customerId and currency ='".$this->currency_iso."'";
			$codeuser = $connection->fetchAll($sql);
			if ($codeuser) {
				if ($codeuser[0]['userCodePayme']) {
					$r = $codeuser[0]['userCodePayme'];
				}
				else try {
					$clientWallet = new \SoapClient($this->configHelper->getConfig('wsdl'));
					$resultWallet = $clientWallet->RegisterCardHolder($paramsWallet);
					$r = $resultWallet->codAsoCardHolderWallet;
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
					$connection = $resource->getConnection();
					$tableName = $resource->getTableName('payme_usercode');
					$sql = "Update " . $tableName . " set userCodePayme = '".$r."' where user_code = $customerId and currency = '".$this->currency_iso."'";
					$connection->query($sql);
				}
				catch (Exception $e) {}
			}
			else try {
				$clientWallet = new \SoapClient($this->configHelper->getConfig('wsdl'));
				$resultWallet = $clientWallet->RegisterCardHolder($paramsWallet);
				$r = $resultWallet->codAsoCardHolderWallet;
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection = $resource->getConnection();
				$tableName = $resource->getTableName('payme_usercode');
				$sql = "Insert Into " . $tableName . " (user_code,currency,userCodePayme) Values ($customerId,'".(string)$d['currency']."','$r')";
				$connection->query($sql);
			} catch (Exception $e) {}
		}
		return $r;
	}
}