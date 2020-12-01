<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Gateway\Config\Config;
use Magento\Store\Model\StoreManagerInterface;
use Vexsoluciones\Credix\Logger\Logger;
use Vexsoluciones\Credix\Model\Payment\Credix;

class AdditionalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Repository
     */
    private $assetRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Vexsoluciones\Credix\Helper\Config
     */
    private $paymentConfig;

    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param Repository $assetRepository
     * @param Logger $logger
     * @param StoreManagerInterface $storeManager
     * @param \Vexsoluciones\Credix\Helper\Config $paymentConfig
     */
    public function __construct(
        Config $config,
        Repository $assetRepository,
        Logger $logger,
        StoreManagerInterface $storeManager,
        \Vexsoluciones\Credix\Helper\Config $paymentConfig
    ) {
        $this->config = $config;
        $this->assetRepository = $assetRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * @return array|array[]
     * @throws NoSuchEntityException
     */
    public function getConfig()
    {
        if ($this->config->getValue('active') == 0) {
            return $this->submitConfig(false);
        }

        return $this->submitConfig(
            true,
            [
                'payment_brand_logo' => $this->getPaymentBrandLogo()
            ]
        );
    }

    /**
     * @param bool $active
     * @param array $data
     * @return array[]
     */
    private function submitConfig(bool $active, array $data = [])
    {
        $response = array_merge(
            $data,
            [
                'status' => $active,
                'isActive' => (string)((int)$active),
                'title' => $this->config->getValue('title'),
                'description' => $this->config->getValue('store_desc'),

                'user' => $this->paymentConfig->getUser(),
                'pass' => $this->paymentConfig->getPassword(),
                'pass_e' => $this->paymentConfig->getHashedPassword(),
            ]
        );

        return [
            'payment' => [
                Credix::PAYMENT_CODE => $response
            ]
        ];
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    private function getPaymentBrandLogo()
    {
        $mediaDirectory = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $uploadImageUrl = $this->config->getValue('upload_image');

        if ($uploadImageUrl === '' || null === $uploadImageUrl) {
            return $this->assetRepository->getUrl('Vexsoluciones_Credix::images/payment_card.png');
        }

        return $mediaDirectory . 'image/' . $uploadImageUrl;
    }
}
