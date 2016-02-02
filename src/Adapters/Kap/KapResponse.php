<?php

namespace Linkstreet\LaravelSms\Adapters\Kap;

use Linkstreet\LaravelSms\Contracts\ResponseInterface;
use Linkstreet\LaravelSms\Collection\DeviceCollection;
use Linkstreet\LaravelSms\Model\Device;

/**
 * KAP Response
 */
class KapResponse implements ResponseInterface
{
    private $devices;

    private $response;

    private $success = [];

    private $failure = [];

    public function __construct(DeviceCollection $devices, $response)
    {
        $this->devices = $devices;
        $this->response = $response;
        $this->processResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessRecipient()
    {
        return new DeviceCollection($this->success);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailureRecipient()
    {
        return new DeviceCollection($this->failure);
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessCount()
    {
        return count($this->success);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailureCount()
    {
        return count($this->failure);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeviceCount()
    {
        return count($this->devices);
    }

    /**
     * {@inheritdoc}
     */
    public function getRaw()
    {
        return $this->response;
    }

    /**
     * Process the given response
     */
    private function processResponse()
    {
        foreach ($this->response->results as $value) {
            if ($value->status == 0) {
                $this->success[$value->destination] = $this->devices->get($value->destination);
            } else {
                $this->failure[$value->destination] = $this->devices->get($value->destination);
            }
        }
    }
}