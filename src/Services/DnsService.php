<?php

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
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
     * https://{_domainconnect}/v2/{domain}/settings
     */
    const DOMAIN_SETTINGS_URL = 'https://%s/v2/%s/settings';

    /**
     * Get Domain settings
     *
     * @param string $domain
     *
     * @return DomainSettings
     *
     * @throws InvalidDomainConnectSettingsException
     * @throws NoDomainConnectSettingsException
     */
    public function getDomainSettings($domain)
    {
        $extractDomain = (new Extract())->parse($domain);
        $subDomain = $extractDomain->getSubdomain();
        $rootDomainName = $this->getRootDomainName($extractDomain);
        $apiUrl = $this->getDomainApiUrl($rootDomainName);
        $apiClient = new Client();

        try {
            $response = $apiClient->request('GET', sprintf(self::DOMAIN_SETTINGS_URL, $apiUrl, $rootDomainName));
            $domainSettings = DomainSettings::loadFromJson($response->getBody()->getContents());

            if (empty($domainSettings->domain)) {
                $domainSettings->domain = $rootDomainName;
            }

            if (!empty($subDomain) && $subDomain !== 'www') {
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
        $domainExtract = new Extract();
        $dnsRecords = (new DnsUtils())->getTxtRecords("_domainconnect.{$rootDomainName}");

        foreach ($dnsRecords as $dnsApiUrl) {
            $domain = $domainExtract->parse($dnsApiUrl);

            if ($domain->isValidDomain()) {
                return $domain->getFullHost();
            }
        }

        throw new NoDomainConnectRecordException("No Domain Connect API found for {$rootDomainName}.");
    }
}
