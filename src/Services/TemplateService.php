<?php

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
use DomainConnect\Exception\InvalidPrivateKeyException;
use DomainConnect\Exception\TemplateNotSupportedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class TemplateService
 */
class TemplateService
{
    /**
     * This URL is be used by the Service Provider to determine if the DNS Provider supports a specific
     * template through the synchronous flow.
     *
     * @var string {urlAPI}/v2/domainTemplates/providers/{providerId}/services/{serviceId}
     */
    const TEMPLATE_CHECK_URL = '%s/v2/domainTemplates/providers/%s/services/%s';

    /**
     * This is the URL where the user is sent to apply a template to a domain they own.
     * It is called from the Service Provider to start the synchronous Domain Connect Protocol.
     *
     * @var string {urlSyncUX}/v2/domainTemplates/providers/{providerId}/services/{serviceId}/apply?[properties]
     */
    const TEMPLATE_APPLY_URL = '%s/v2/domainTemplates/providers/%s/services/%s/apply?%s';

    /**
     * @var string Signature algorithm
     */
    const SIGNATURE_ALG = 'sha256WithRSAEncryption';

    /**
     * @var DnsService
     */
    private $dnsService;

    /**
     * @var Client
     */
    private $client;

    public function __construct(array $clientConfig = [])
    {
        $this->client = new Client($clientConfig);
        $this->dnsService = new DnsService($this->client);
    }

    /**
     * Makes full Domain Connect discovery of a domain and returns full url to request sync consent.
     *
     * @param string $domain
     * @param string $providerId
     * @param string $serviceId
     * @param array  optional $params
     * @param string optional $privateKey RSA key in PEM format
     * @param string optional $keyId      host name of the TXT record with public KEY (appended to syncPubKeyDomain)
     *
     * @return string
     *
     * @throws TemplateNotSupportedException
     * @throws InvalidDomainConnectSettingsException
     */
    public function getTemplateSyncUrl(
        $domain,
        $providerId,
        $serviceId,
        $params = null,
        $privateKey = null,
        $keyId = null
    ) {
        $params = $params ?: [];
        $domainSettings = $this->dnsService->getDomainSettings($domain);

        if (!$this->isTemplateSupported($providerId, $serviceId, $domainSettings)) {
            throw new TemplateNotSupportedException(
                sprintf('No template for serviceId: %s from %s', $serviceId, $providerId)
            );
        }

        if (!$domainSettings->urlSyncUX) {
            throw new InvalidDomainConnectSettingsException('No sync URL in config');
        }

        $params['domain'] = $domainSettings->domain;

        if (!empty($domainSettings->host)) {
            $params['host'] = $domainSettings->host;
        }

        ksort($params, SORT_NATURAL | SORT_FLAG_CASE);

        if ($privateKey && $keyId) {
            $params['sig'] = $this->generateSign($privateKey, http_build_query($params));
            $params['key'] = $keyId;
        }

        return sprintf(
            self::TEMPLATE_APPLY_URL,
            $domainSettings->urlSyncUX,
            $providerId,
            $serviceId,
            http_build_query($params)
        );
    }

    /**
     * Check is template supported
     *
     * @param string         $providerId
     * @param string         $serviceId
     * @param DomainSettings $domainSettings
     *
     * @return bool
     */
    public function isTemplateSupported($providerId, $serviceId, DomainSettings $domainSettings)
    {
        try {
            $response = $this->client->request(
                'GET',
                sprintf(self::TEMPLATE_CHECK_URL, $domainSettings->urlAPI, $providerId, $serviceId)
            );

            return $response->getStatusCode() === 200;
        } catch (ClientException $e) {
            // Status 404 indicates that current template doesn't support
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Computes a signature for the specified query
     *
     * @param string $privateKey Private key
     * @param string $query      Data
     *
     * @return string
     *
     * @throws InvalidPrivateKeyException
     */
    private function generateSign($privateKey, $query)
    {
        $key = openssl_pkey_get_private($privateKey);

        if (!$key) {
            $openSSLErrors = [];

            while ($error = openssl_error_string()) {
                $openSSLErrors[] = $error;
            }

            throw new InvalidPrivateKeyException('Private key is invalid: ' . json_encode($openSSLErrors));
        }

        //Generate signature
        openssl_sign($query, $signature, $key, self::SIGNATURE_ALG);

        return base64_encode($signature);
    }
}
