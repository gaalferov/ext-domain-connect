<?php

namespace DomainConnect\Services;

use GuzzleHttp\Client;
use LayerShifter\TLDExtract\ResultInterface;

/**
 * Class BaseService
 */
class BaseService
{
    /**
     * @var Client
     */
    public $apiClient;

    /**
     * @var ResultInterface
     */
    public $domain;

    /**
     * BaseService constructor.
     *
     * @param Client $apiClient
     */
    public function __construct(Client $apiClient, ResultInterface $domain)
    {
        $this->apiClient = $apiClient;
        $this->domain = $domain;
    }
}
