<?php

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
use DomainConnect\Exception\InvalidDomainException;
use DomainConnect\Exception\NoDomainConnectRecordException;
use DomainConnect\Exception\NoDomainConnectSettingsException;
use DomainConnect\Services\Utils\DnsUtils;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use LayerShifter\TLDExtract\Extract;
use LayerShifter\TLDExtract\ResultInterface;

/**
 * Class Dns
 */
class DnsService
{
    /**
     * The URL prefix returned is subsequently used by the Service Provider to determine the additional settings
     * for using Domain Connect on this domain at the DNS Provider.
     *
     * @var string https://{_domainconnect}/v2/{domain}/settings
     */
    const DOMAIN_SETTINGS_URL = 'https://%s/v2/%s/settings';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Extract
     */
    private $domainExtractor;

    /**
     * @var DnsUtils
     */
    private $dnsUtils;

    /**
     * DnsService constructor.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->domainExtractor = new Extract();
        $this->dnsUtils = new DnsUtils();
    }

    /**
     * Get Domain settings
     *
     * @param string $domain
     *
     * @return DomainSettings
     *
     * @throws InvalidDomainConnectSettingsException
     * @throws NoDomainConnectSettingsException
     * @throws InvalidDomainException
     */
    public function getDomainSettings($domain)
    {
        $extractDomain = $this->domainExtractor->parse($domain);

        if (!$extractDomain->isIp() && !$extractDomain->isValidDomain()) {
            throw new InvalidDomainException('Invalid domain name: ' . $domain);
        }

        $subDomain = $extractDomain->getSubdomain();
        $rootDomainName = $this->getRootDomainName($extractDomain);
        $apiUrl = $this->getDomainApiUrl($rootDomainName);

        try {
            $response = $this->client->request('GET', sprintf(self::DOMAIN_SETTINGS_URL, $apiUrl, $rootDomainName));
            $domainSettings = DomainSettings::loadFromJson($response->getBody()->getContents());

            if (empty($domainSettings->domain)) {
                $domainSettings->domain = $rootDomainName;
            }

            if (!empty($subDomain)) {
                $domainSettings->host = $subDomain;
            }

            return $domainSettings;
        } catch (ClientException $e) {
            //A response of a 404 indicates that the DNS Provider does not have the zone.
            throw new NoDomainConnectSettingsException(
                'Domain does not support domain connect. You would need to make DNS changes manually.'
            );
        } catch (Exception $e) {
            throw new InvalidDomainConnectSettingsException(
                'Cannot fetch DomainConnect Settings. Error: '. $e->getMessage()
            );
        }
    }

    /**
     * @param ResultInterface $domain
     *
     * @return string
     */
    private function getRootDomainName(ResultInterface $domain)
    {
        if ($domain->isIp()) {
            return $domain->getHostname();
        }

        return $domain->getRegistrableDomain();
    }

    /**
     * Get domain api url
     *
     * @param $rootDomainName string Domain name
     *
     * @return string
     *
     * @throws NoDomainConnectRecordException
     */
    private function getDomainApiUrl($rootDomainName)
    {
        $dnsRecords = $this->dnsUtils->getTxtRecords("_domainconnect.{$rootDomainName}");

        foreach ($dnsRecords as $dnsApiUrl) {
            $domain = $this->domainExtractor->parse($dnsApiUrl);

            if ($domain->isValidDomain()) {
                return $domain->getFullHost();
            }
        }

        throw new NoDomainConnectRecordException("No Domain Connect API found for {$rootDomainName}.");
    }
}
