<?php

declare(strict_types=1);

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
use DomainConnect\Exception\InvalidDomainException;
use DomainConnect\Exception\InvalidPrivateKeyException;
use DomainConnect\Exception\NoDomainConnectRecordException;
use DomainConnect\Exception\NoDomainConnectSettingsException;
use DomainConnect\Exception\TemplateNotSupportedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class TemplateService.
 */
class TemplateService
{
    /**
     * @var string Signature algorithm
     */
    public const SIGNATURE_ALG = 'sha256WithRSAEncryption';

    /**
     * This URL is be used by the Service Provider to determine if the DNS Provider supports a specific
     * template through the synchronous flow.
     *
     * @var string {urlAPI}/v2/domainTemplates/providers/{providerId}/services/{serviceId}
     */
    public const TEMPLATE_CHECK_URL = '%s/v2/domainTemplates/providers/%s/services/%s';

    /**
     * This is the URL where the user is sent to apply a template to a domain they own.
     * It is called from the Service Provider to start the synchronous Domain Connect Protocol.
     *
     * @var string {urlSyncUX}/v2/domainTemplates/providers/{providerId}/services/{serviceId}/apply?[properties]
     */
    public const TEMPLATE_APPLY_URL = '%s/v2/domainTemplates/providers/%s/services/%s/apply?%s';

    private DnsService $dnsService;

    private Client $client;

    public function __construct(array $clientConfig = [])
    {
        $this->client = new Client($clientConfig);
        $this->dnsService = new DnsService($this->client);
    }

    /**
     * Makes full Domain Connect discovery of a domain and returns full url to request sync consent.
     *
     * @throws InvalidDomainConnectSettingsException
     * @throws InvalidDomainException
     * @throws InvalidPrivateKeyException
     * @throws NoDomainConnectRecordException
     * @throws NoDomainConnectSettingsException
     * @throws TemplateNotSupportedException
     */
    public function getTemplateSyncUrl(
        string $domain,
        string $providerId,
        string $serviceId,
        array $params = null,
        string $privateKey = null,
        string $keyId = null
    ): string {
        $params = $params ?: [];
        $domainSettings = $this->dnsService->getDomainSettings($domain);

        if (!$this->isTemplateSupported($providerId, $serviceId, $domainSettings)) {
            throw new TemplateNotSupportedException(
                sprintf('No template for serviceId: %s from %s', $serviceId, $providerId)
            );
        }

        if (!$domainSettings->getUrlSyncUX()) {
            throw new InvalidDomainConnectSettingsException('No sync URL in config');
        }

        $params['domain'] = $domainSettings->getDomain();

        if (!empty($domainSettings->getHost())) {
            $params['host'] = $domainSettings->getHost();
        }

        ksort($params, SORT_NATURAL | SORT_FLAG_CASE);

        if ($privateKey && $keyId) {
            $params['sig'] = $this->generateSign($privateKey, http_build_query($params));
            $params['key'] = $keyId;
        }

        return sprintf(
            self::TEMPLATE_APPLY_URL,
            $domainSettings->getUrlSyncUX(),
            $providerId,
            $serviceId,
            http_build_query($params)
        );
    }

    /**
     * Check is template supported.
     */
    public function isTemplateSupported(string $providerId, string $serviceId, DomainSettings $domainSettings): bool
    {
        try {
            $response = $this->client->request(
                'GET',
                sprintf(self::TEMPLATE_CHECK_URL, $domainSettings->getUrlAPI(), $providerId, $serviceId)
            );

            return 200 === $response->getStatusCode();
        } catch (ClientException $e) {
            // Status 404 indicates that current template doesn't support
            if ($e->getResponse() && 404 === $e->getResponse()->getStatusCode()) {
                return false;
            }

            throw $e;
        }
    }

    /**
     * Computes a signature for the specified query.
     *
     * @param string $privateKey Private key
     * @param string $query      Data
     *
     * @throws InvalidPrivateKeyException
     * @throws \JsonException
     */
    private function generateSign(string $privateKey, string $query): string
    {
        $key = openssl_pkey_get_private($privateKey);

        if (!$key) {
            $openSSLErrors = [];

            while ($error = openssl_error_string()) {
                $openSSLErrors[] = $error;
            }

            throw new InvalidPrivateKeyException(
                'Private key is invalid: '.json_encode($openSSLErrors, JSON_THROW_ON_ERROR)
            );
        }

        // Generate signature
        openssl_sign($query, $signature, $key, self::SIGNATURE_ALG);

        return base64_encode($signature);
    }
}
