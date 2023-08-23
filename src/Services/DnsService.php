<?php

declare(strict_types=1);

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
use DomainConnect\Exception\InvalidDomainException;
use DomainConnect\Exception\NoDomainConnectRecordException;
use DomainConnect\Exception\NoDomainConnectSettingsException;
use DomainConnect\Services\Utils\DnsUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class Dns.
 */
class DnsService
{
    /**
     * The URL prefix returned is subsequently used by the Service Provider to determine the additional settings
     * for using Domain Connect on this domain at the DNS Provider.
     *
     * @var string https://{_domainconnect}/v2/{domain}/settings
     */
    public const DOMAIN_SETTINGS_URL = 'https://%s/v2/%s/settings';

    private Client $client;

    private DnsUtils $dnsUtils;

    /**
     * DnsService constructor.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->dnsUtils = new DnsUtils();
    }

    /**
     * Get Domain settings.
     *
     * @throws InvalidDomainConnectSettingsException
     * @throws NoDomainConnectSettingsException
     * @throws InvalidDomainException
     * @throws NoDomainConnectRecordException
     */
    public function getDomainSettings(string $domain): DomainSettings
    {
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidDomainException('Invalid domain name: '.$domain);
        }

        $apiUrl = $this->getDomainApiUrl($domain);

        try {
            $response = $this->client->request('GET', sprintf(self::DOMAIN_SETTINGS_URL, $apiUrl, $domain));

            return DomainSettings::loadFromJson($response->getBody()->getContents(), $domain);
        } catch (ClientException $e) {
            // A response of a 404 indicates that the DNS Provider does not have the zone.
            throw new NoDomainConnectSettingsException(
                'Domain does not support domain connect. You would need to make DNS changes manually.'
            );
        } catch (\Exception $e) {
            throw new InvalidDomainConnectSettingsException(
                'Cannot fetch DomainConnect Settings. Error: '.$e->getMessage()
            );
        }
    }

    /**
     * Get domain api url.
     *
     * @param string $domain Domain name
     *
     * @throws NoDomainConnectRecordException
     */
    private function getDomainApiUrl(string $domain): string
    {
        $dnsRecords = $this->dnsUtils->getTxtRecords("_domainconnect.{$domain}");

        foreach ($dnsRecords as $dnsApiUrl) {
            if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                return $dnsApiUrl;
            }
        }

        throw new NoDomainConnectRecordException("No Domain Connect API found for {$domain}.");
    }
}
