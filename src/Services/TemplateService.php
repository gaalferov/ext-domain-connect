<?php

namespace DomainConnect\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainConnectSettingsException;
use DomainConnect\Exception\TemplateNotSupportedException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use LayerShifter\TLDExtract\ResultInterface;

/**
 * Class TemplateService
 */
class TemplateService extends BaseService
{
    /**
     * This URL is be used by the Service Provider to determine if the DNS Provider supports a specific
     * template through the synchronous flow.
     *
     * {urlAPI}/v2/domainTemplates/providers/{providerId}/services/{serviceId}
     */
    const TEMPLATE_CHECK_URL = '%s/v2/domainTemplates/providers/%s/services/%s';

    /**
     * This is the URL where the user is sent to apply a template to a domain they own.
     * It is called from the Service Provider to start the synchronous Domain Connect Protocol.
     *
     * {urlSyncUX}/v2/domainTemplates/providers/{providerId}/services/{serviceId}/apply?[properties]
     */
    const TEMPLATE_APPLY_URL = '%s/v2/domainTemplates/providers/%s/services/%s/apply?%s';

    /**
     * @var DomainSettings
     */
    public $domainSettings;

    public function __construct(Client $apiClient, ResultInterface $domain, DomainSettings $domainSettings)
    {
        parent::__construct($apiClient, $domain);

        $this->domainSettings = $domainSettings;
    }

    /**
     * Makes full Domain Connect discovery of a domain and returns full url to request sync consent.
     *
     * @param string $providerId
     * @param string $serviceId
     * @param array  optional $params
     * @param string optional $privateKey RSA key in PEM format
     * @param string optional $keyid      host name of the TXT record with public KEY (appended to syncPubKeyDomain)
     *
     * @return string
     *
     * @throws TemplateNotSupportedException
     * @throws InvalidDomainConnectSettingsException
     */
    public function getTemplateSyncUrl(
        $providerId,
        $serviceId,
        $params = null,
        $privateKey = null,
        $keyid = null
    ) {
        $params = $params ?: [];
        $subDomain = $this->domain->getSubdomain();

        if (!$this->isTemplateSupported($providerId, $serviceId)) {
            throw new TemplateNotSupportedException(sprintf(
                    'No template for serviceId: %s from %s',
                    $serviceId,
                    $providerId)
            );
        }

        if (!$this->domainSettings->urlSyncUX) {
            throw new InvalidDomainConnectSettingsException('No sync URL in config');
        }

        if (!empty($subDomain) && $subDomain !== 'www') {
            $params['host'] = $subDomain;
        }

        $params = array_merge([
            'domain' => $this->domainSettings->domain,
            'providerName' => $this->domainSettings->providerDisplayName ?: $this->domainSettings->providerName,
        ], $params);
        ksort($params, SORT_NATURAL | SORT_FLAG_CASE);

        //TODO implement functional $privateKey and $keyid
        return sprintf(
            self::TEMPLATE_APPLY_URL,
            $this->domainSettings->urlSyncUX,
            $providerId,
            $serviceId,
            http_build_query($params)
        );
    }

    /**
     * @param string $providerId
     * @param string $serviceId
     *
     * @return bool
     */
    public function isTemplateSupported($providerId, $serviceId)
    {
        try {
            $response = $this->apiClient->request(
                'GET',
                sprintf(self::TEMPLATE_CHECK_URL, $this->domainSettings->urlAPI, $providerId, $serviceId)
            );

            return $response->getStatusCode() === 200;
        } catch (ClientException $e) {
            //Returning a status of 404 indicates the template is not supported.
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                return false;
            }

            throw $e;
        }
    }
}
