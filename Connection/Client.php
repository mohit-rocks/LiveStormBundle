<?php

declare(strict_types=1);

namespace MauticPlugin\LiveStormBundle\Connection;

use GuzzleHttp\Exception\ClientException;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\LiveStormBundle\Integration\Config;
use MauticPlugin\LiveStormBundle\Integration\LiveStormIntegration;
use Monolog\Logger;

class Client
{
    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Logger $logger,
        Config $config
    ) {
        $this->httpClient       = new \GuzzleHttp\Client();
        $this->logger           = $logger;
        $this->config           = $config;

        // Get the API keys and initialize API Host.
        $apiKeys                = $this->config->getApiKeys();
        //$this->apiUrl           = $apiKeys['host'];
        $this->apiUrl           = 'https://api.livestorm.co/v1';
    }

    /**
     * Validate the credentials using secret key and API Url.
     *
     * @param string $apiUrl
     *                       Livestorm API endpoint URL
     * @param string $secret
     *                       Livestorm Secret key
     *
     * @return bool
     *              True if credentials are valid, false otherwise
     */
    public function validateCredentials(string $apiUrl, string $secret): bool
    {
        try {
            $response = $this->httpClient->get($apiUrl.'/ping', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => $secret,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->logger->error(
                sprintf(
                    '%s: Error validating API credential: %s',
                    LiveStormIntegration::DISPLAY_NAME,
                    $response->getReasonPhrase()
                )
            );
        }

        if (200 !== (int) $response->getStatusCode()) {
            return false;
        }

        return true;
    }

    /**
     * Fetch the API data from the endpoint.
     *
     * @param string $url
     *                    API endpoint URL
     *
     * @return array
     *               Array with values or empty array
     */
    public function get(string $url)
    {
        $credentials = $this->getCredentials();

        try {
            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $this->httpClient->get($this->apiUrl.$url, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => $credentials->getApiSecret(),
                ],
            ]);
            if (200 == $response->getStatusCode()) {
                $responseData = $response->getBody()->getContents();
                $data         = json_decode((string) $responseData, true);
                if (!empty($data['data'])) {
                    return $data['data'];
                }

                return [];
            }
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->logger->error(
                sprintf(
                    'Something went wrong with the request. Please check API endpoints.',
                    LiveStormIntegration::DISPLAY_NAME,
                    $response->getReasonPhrase()
                )
            );
        }
    }

    /**
     * Create new Credentials object for use in other methods.
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     */
    private function getCredentials(): Credentials
    {
        if (!$this->config->isConfigured()) {
            throw new PluginNotConfiguredException();
        }

        $apiKeys = $this->config->getApiKeys();

        return new Credentials($apiKeys['secret']);
    }
}
