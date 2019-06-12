<?php

namespace DomainConnect;

use DomainConnect\Services\DnsService;
use DomainConnect\Services\TemplateService;
use GuzzleHttp\Client;
use LayerShifter\TLDExtract\Extract;

/**
 * Class DomainConnect
 */
class DomainConnect
{
    /**
     * @var DnsService
     */
    public $dnsService;

    /**
     * @var TemplateService
     */
    public $templateService;

    /**
     * DomainConnect constructor.
     *
     * @param Client $apiClient
     * @param string $domain
     */
    public function __construct(Client $apiClient, $domain)
    {
        $resultDomainInfo = (new Extract())->parse($domain);

        $this->dnsService = new DnsService($apiClient, $resultDomainInfo);
        $this->templateService = new TemplateService(
            $apiClient,
            $resultDomainInfo,
            $this->dnsService->getDomainSettings()
        );
    }
}
