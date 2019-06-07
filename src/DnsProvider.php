<?php

namespace DomainConnect;

use DomainConnect\Exception\NoDomainConnectRecordException;
use DomainConnect\Exception\NoDomainConnectSettingsException;
use DomainConnect\Utils\DnsUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use LayerShifter\TLDExtract\Extract;

/**
 * Class DnsProvider
 */
class DnsProvider
{
    /**
     * @var Client
     */
    private $apiClient;

    /**
     * DnsProvider constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->apiClient = $client;
    }

    /**
     * @param $domain
     */
    public function getDomainConfig($domain)
    {
        $domainName = $this->getRootDomainName($domain);
        $apiUrl = $this->getDomainApiUrl($domainName);

        try {
            $response = $this->apiClient->request('GET', "https://{$apiUrl}/v2/{$domainName}/settings");
        } catch (TransferException $e) {
            throw new NoDomainConnectSettingsException(
                'Cannot fetch DomainConnect Settings. Error: '. $e->getMessage()
            );
        }

        $response = $this->apiClient->request('GET', "https://{$apiUrl}/v2/{$domainName}/settings");
        var_dump($response);
    }

    /**
     * @param $domain
     *
     * @return string|null
     */
    private function getRootDomainName($domain)
    {
        $domainExtract = (new Extract())->parse($domain);

        if ($domainExtract->isIp()) {
            return $domainExtract->getHostname();
        }

        return $domainExtract->getRegistrableDomain();
    }

    /**
     * @param $domainName
     *
     * @return mixed
     * @throws NoDomainConnectRecordException
     */
    private function getDomainApiUrl($domainName)
    {
        $domainExtract = new Extract();
        $dnsRecords = (new DnsUtils())->getTxtRecords("_domainconnect.{$domainName}");

        foreach ($dnsRecords as $dnsApiUrl) {
            $domain = $domainExtract->parse($dnsApiUrl);

            if ($domain->isValidDomain()) {
                return $domain->getFullHost();
            }
        }

        throw new NoDomainConnectRecordException("No Domain Connect API found for {$domainName}.");
    }
}
