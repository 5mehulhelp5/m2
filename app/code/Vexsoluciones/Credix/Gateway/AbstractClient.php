<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Gateway;

use Vexsoluciones\Credix\Exceptions\ClientException;
use Vexsoluciones\Credix\Helper\Config;
use Vexsoluciones\Credix\Logger\Logger;

abstract class AbstractClient
{
    /**
     * @var string
     */
    protected $integrationUrl = '';

    /**
     * @var string
     */
    protected $productionUrl = '';

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Logger $logger,
        Config $config
    )
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param string $resource
     * @param array $request
     * @param array $headers
     * @return array
     * @throws ClientException
     */
    protected function performPost(string $resource, array $request, array $headers)
    {
        $headers = $this->buildHeaders($headers);

        $this->logger->info(sprintf('Resource called: %s, Environment: %s.', $this->getResourceUrl($resource), $this->config->getEnvironmentLabel()));
        $this->logger->info('[POST] Headers', $headers);
        $this->logger->obscureAndInfo('[POST] Request', $request);

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            [
                CURLOPT_URL => $this->getResourceUrl($resource),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($request),
                CURLOPT_HTTPHEADER => $headers,
            ]
        );

        $content = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $information = curl_getinfo($ch);

        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            throw ClientException::generalException($error);
        }

        curl_close($ch);

        $content = json_decode($content, true);
        $this->logger->info('Resource content:', [$content]);

        if (isset($content['type']) && $content['type'] === 'error') {
            $this->logger->warning('Resource content:', [$content]);
            throw ClientException::generalException($content['message']);
        }

        return $content;
    }

    /**
     * @param array $headers
     * @return array
     */
    private function buildHeaders(array $headers = []): array
    {
        return array_merge([
            'Accept: application/json',
            'Content-Type: text/json',
        ], $headers);
    }

    /**
     * @param string $resource
     * @return string
     */
    protected function getResourceUrl(string $resource)
    {
        if ($this->config->isEnvironmentProductionMode()) {
            return $this->productionUrl . $resource;
        }

        return $this->integrationUrl . $resource;
    }
}
