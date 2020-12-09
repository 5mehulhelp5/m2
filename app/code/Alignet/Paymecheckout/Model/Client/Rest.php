<?php
namespace Alignet\Paymecheckout\Model\Client;
final class Rest extends \Alignet\Paymecheckout\Model\Client {
	/**
	 * @param Rest\Config $configHelper
	 * @param Rest\Order $orderHelper
	 */
	function __construct(
		Rest\Config $configHelper,
		Rest\Order $orderHelper
	) {
		parent::__construct(
			$configHelper,
			$orderHelper
		);
	}
}