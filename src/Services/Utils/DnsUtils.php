<?php

declare(strict_types=1);

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
     * @param string $domain Domain name
     *
     * @return string
     * @throws NoDomainConnectRecordException
     */
    public function getARecord(string $domain): string
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
     * @param string $domain Domain name
     *
     * @return array
     * @throws NoDomainConnectRecordException
     */
    public function getTxtRecords(string $domain): array
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
     * Get DNS Records by type
     *
     * @param string $domain
     * @param int $type
     *
     * @return array
     * @throws NoDomainConnectRecordException
     */
    private function getDnsRecordsByType(string $domain, int $type): array
    {
        $dnsRecord = @dns_get_record($domain, $type);

        if (false === $dnsRecord) {
            $error = error_get_last()['message'];

            throw new NoDomainConnectRecordException("Failed to resolve {$domain}: {$error}");
        }

        return $dnsRecord;
    }
}
