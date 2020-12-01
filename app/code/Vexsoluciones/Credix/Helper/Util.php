<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Vexsoluciones\Credix\Model\Payment\Credix;

class Util
{
    const ITEM_LICENSE_SECRET_KEY = '587423b988e403.69821411';
    const ITEM_LICENSE_SERVER_URL = 'https://www.pasarelasdepagos.com';
    const ITEM_ITEM_REFERENCE = 'Credix - Magento 2';

    protected $scopeConfig;
    protected $configWriter;
    protected $messageManager;
    protected $logger;

    private $code = '';

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ManagerInterface $messageManager,
        WriterInterface $configWriter,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
        $this->logger = $logger;

        $this->code = Credix::PAYMENT_CODE;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function verify()
    {
        $license = $this->scopeConfig->getValue('payment/' . $this->code . '/license', ScopeInterface::SCOPE_STORE);

        if (empty($license)) {
            $this->disabled();
            return;
        }

        $this->configWriter->save('payment/' . $this->code . '/activated', '0', 'default', 0);
        $this->configWriter->save('payment/' . $this->code . '/last_date', '', 'default', 0);
        $activated = $this->scopeConfig->getValue('payment/' . $this->code . '/activated', ScopeInterface::SCOPE_STORE);
        $last_date = $this->scopeConfig->getValue('payment/' . $this->code . '/last_date', ScopeInterface::SCOPE_STORE);

        $current_date = date('d/m/Y');

        if (empty($last_date) && $activated) {
            return;
        }

        if ($last_date != $current_date) {
            $this->_verify($license);
        }
    }

    private function disabled()
    {
        $this->configWriter->save('payment/' . $this->code . '/active', '0', 'default', 0);
        $this->configWriter->save('payment/' . $this->code . '/setting', '', 'default', 0);

        $settings = $this->scopeConfig->getValue('payment/' . $this->code . '/setting', ScopeInterface::SCOPE_STORE);

        if (empty($settings)) {
            return;
        }

        $this->configWriter->save('payment/' . $this->code . '/setting', $settings, 'default', 0);
        $this->configWriter->save('payment/' . $this->code . '/activated', '0', 'default', 0);
    }

    private function _verify($license)
    {
        $curl = curl_init(
            self::ITEM_LICENSE_SERVER_URL . "?license_key=" . $license . "&slm_action=slm_check&secret_key=" . self::ITEM_LICENSE_SECRET_KEY
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json"
            )
        );
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 != $http_status && !empty($response)) {
            $this->messageManager->addError(__($http_status . ' - ' . $response));
            return;
        } elseif (200 != $http_status) {
            $this->messageManager->addError(__('Unexpected Error! The query returned with an error.'));
            return;
        }

        $license_data = json_decode($response, true);

        if (isset($license_data['result']) && $license_data['result'] == 'success') {
            $this->_activate_plugin($license_data['message']);

            $license_registered = $this->scopeConfig->getValue(
                'payment/' . $this->code . '/license_registered',
                ScopeInterface::SCOPE_STORE
            );

            if ($license_registered == '' && $license != $license_registered) {
                $this->activate($license);
            }
            return;
        }

        $this->configWriter->save('payment/' . $this->code . '/activated', '0', 'default', 0);
        $this->disabled();
    }

    private function _activate_plugin($message)
    {
        $this->configWriter->save('payment/' . $this->code . '/activated', '1', 'default', 0);
        $this->configWriter->save('payment/' . $this->code . '/last_date', date('d/m/Y'), 'default', 0);
    }

    public function activate($license)
    {
        $curl = curl_init(
            self::ITEM_LICENSE_SERVER_URL . "?license_key=" . $license . "&slm_action=slm_activate&secret_key=" . self::ITEM_LICENSE_SECRET_KEY . "&item_reference=" . urlencode(
                self::ITEM_ITEM_REFERENCE
            ) . "&registered_domain=" . $_SERVER['SERVER_NAME']
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt(
            $curl,
            CURLOPT_HTTPHEADER,
            array(
                "Content-Type: application/json"
            )
        );
        $response = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


        if (200 != $http_status && !empty($response)) {
            $this->messageManager->addError(__($http_status . ' - ' . $response));
            return;
        } elseif (200 != $http_status) {
            $this->messageManager->addError(__('Unexpected Error! The query returned with an error.'));

            return;
        }

        $license_data = json_decode($response, true);

        if (isset($license_data['result']) && $license_data['result'] == 'success') {
            $this->configWriter->save('payment/' . $this->code . '/license_registered', $license, 'default', 0);
            $this->_activate_plugin($license_data['message']);
            return;
        }

        $this->messageManager->addError(__($license_data['message']));
        $this->disabled();
    }

}
