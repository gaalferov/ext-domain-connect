<?php

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
use DomainConnect\Exception\NoDomainConnectRecordException;
use DomainConnect\Exception\NoDomainConnectSettingsException;
use DomainConnect\Services\Utils\DnsUtils;
use Exception;
use GuzzleHttp\Exception\ClientException;
use LayerShifter\TLDExtract\Extract;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class Dns
 */
class DnsService extends BaseService
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
     * @return DomainSettings
     *
     * @throws NoDomainConnectSettingsException
     * @throws InvalidDomainConnectSettingsException
     */
    public function getDomainSettings()
    {
        $rootDomainName = $this->getRootDomainName();
        $apiUrl = $this->getDomainApiUrl($rootDomainName);

        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        try {
            $response = $this->apiClient->request('GET', sprintf(self::DOMAIN_SETTINGS_URL, $apiUrl, $rootDomainName));

            /** @var DomainSettings $domainSettings */
            $domainSettings = $serializer->deserialize(
                $response->getBody()->getContents(),
                DomainSettings::class,
                'json'
            );

            if (empty($domainSettings->domain)) {
                $domainSettings->domain = $rootDomainName;
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
     * @return string
     */
    private function getRootDomainName()
    {
        if ($this->domain->isIp()) {
            return $this->domain->getHostname();
        }

        return $this->domain->getRegistrableDomain();
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
