<?php

namespace Linkstreet\LaravelSms\Adapters\Kap;

use GuzzleHttp\Psr7\Request;
use Linkstreet\LaravelSms\Adapters\HttpClient;
use Linkstreet\LaravelSms\Contracts\AdapterInterface;
use Linkstreet\LaravelSms\Contracts\ResponseInterface;
use Linkstreet\LaravelSms\Exceptions\AdapterException;
use Linkstreet\LaravelSms\Model\Device;

/**
 * KapAdapter
 */
class KapAdapter implements AdapterInterface
{
    use HttpClient;

    /**
     * @var array
     */
    private $config;

    /**
     * Create a instance of Kap Adapter
     * @param array configuration for KAP adapter
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        $this->setClient();
    }

    /**
     * {@inheritdoc}
     */
    public function send(Device $device, string $message): ResponseInterface
    {
        $this->checkForMissingConfiguration();

        $response = $this->client->send($this->buildRequest(), $this->buildOptions($device, $message));

        return new KapResponse($device, $response);
    }

    /**
     * Build Guzzle request object
     * @return Request
     */
    private function buildRequest(): Request
    {
        return new Request('POST', 'https://api.kapsystem.com/api/v3/sendsms/json');
    }

    /**
     * Build Guzzle query options with json payload
     * @param Device $device
     * @param string $message
     * @return array
     */
    private function buildOptions(Device $device, string $message): array
    {
        return [
            'debug' => false,
            'verify' => false,
            'timeout' => 20,
            'json' => [
                'authentication' => [
                    'username' => $this->config['username'],
                    'password' => $this->config['password']
                ],
                'messages' => [
                    [
                        'sender' => $this->config['sender'],
                        'text' => $message,
                        'type' => 'longSMS',
                        'recipients' => [
                            ['gsm' => $device->getNumberWithoutPlusSign()]
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Check for valid configuration
     * @throws AdapterException
     */
    private function checkForMissingConfiguration()
    {
        $config = $this->config;

        if (!isset($config['username'], $config['password'], $config['sender'])) {
            throw AdapterException::missingConfiguration();
        }
    }
}
