<?php

namespace DomainConnect\Services\Utils;

use DomainConnect\Exception\NoDomainConnectRecordException;

/**
 * Class DnsUtils
 */
class DnsUtils
{
    /**
     * Get DNS_A record
     *
     * @param $domain string Domain name
     *
     * @return string
     * @throws NoDomainConnectRecordException
     */
    public function getARecord($domain)
    {
        $dnsRecord = $this->getDnsRecordsByType($domain, DNS_A | DNS_AAAA);

        if (!isset($dnsRecord[0]['ip']) && !isset($dnsRecord[0]['ipv6'])) {
            throw new NoDomainConnectRecordException("Couldn't find A/AAAA DNS record for {$domain}.");
        }

        return $dnsRecord[0]['ip'] ?: $dnsRecord[0]['ipv6'];
    }

    /**
     * Get DNS_TXT records
     *
     * @param $domain string Domain name
     *
     * @return array
     * @throws NoDomainConnectRecordException
     */
    public function getTxtRecords($domain)
    {
        $txtRecords = array_filter(array_map(
            function ($record) {
                return $record['txt'];
            },
            $this->getDnsRecordsByType($domain, DNS_TXT)
        ));

        if (empty($txtRecords)) {
            throw new NoDomainConnectRecordException("Couldn't find TXT DNS records for {$domain}.");
        }

        return $txtRecords;
    }

    /**
     * Get DNS_MX record
     *
     * @param $domain string Domain name
     *
     * @return array
     * @throws NoDomainConnectRecordException
     */
    public function getMxRecord($domain)
    {
        $dnsRecord = $this->getDnsRecordsByType($domain, DNS_MX);

        if (!isset($dnsRecord[0]['host'])) {
            throw new NoDomainConnectRecordException("Couldn't find MX DNS record for {$domain}.");
        }

        return [
            'host' => $dnsRecord[0]['host'],
            'ip' => $this->getARecord($dnsRecord[0]['host']),
            'priority' => $dnsRecord[0]['pri']
        ];
    }

    /**
     * Get DNS Records by type
     *
     * @param $domain
     * @param $type
     *
     * @return array
     * @throws NoDomainConnectRecordException
     */
    private function getDnsRecordsByType($domain, $type)
    {
        $dnsRecord = @dns_get_record($domain, $type);

        if (false === $dnsRecord) {
            $error = error_get_last()['message'];

            throw new NoDomainConnectRecordException("Failed to resolve {$domain}: {$error}");
        }

        return $dnsRecord;
    }
}
