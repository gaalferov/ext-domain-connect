<?php

declare(strict_types=1);

namespace DomainConnect\DTO;

/**
 * Class DomainSettings.
 */
class DomainSettings
{
    /**
     * Unique identifier for the DNS Provider.
     * To ensure non-coordinated uniqueness, this should be the domain name of the DNS Provider (e.g. virtucom.com).
     */
    private string $providerId = '';

    /**
     * The name of the DNS Provider.
     */
    private string $providerName = '';

    /**
     * The URL Prefix for the REST API.
     */
    private string $urlAPI = '';

    /**
     * (optional) The name of the DNS Provider that should be displayed by the Service Provider.
     * This may change per domain for some DNS Providers that power multiple brands.
     */
    private ?string $providerDisplayName = null;

    /**
     * Root Domain Name.
     */
    private string $domain = '';

    /**
     * (optional) Sub domain.
     */
    private string $host = '';

    /**
     * (optional) The URL Prefix for linking to the UX of Domain Connect for the synchronous flow at the DNS Provider.
     * If not returned, the DNS Provider is not supporting the synchronous flow on this domain.
     */
    private ?string $urlSyncUX = null;

    /**
     * (optional) The URL Prefix for linking to the UX elements of Domain Connect for the asynchronous
     * flow at the DNS Provider.
     * If not returned, the DNS Provider is not supporting the asynchronous flow on this domain.
     */
    private ?string $urlAsyncUX = null;

    /**
     * (optional) This is the desired width of the window for granting consent when navigated in a popup.
     * Default value if not returned should be 750px.
     */
    private int $width = 750;

    /**
     * (optional) This is the desired height of the window for granting consent when navigated in a popup.
     * Default value if not returned should be 750px.
     */
    private int $height = 750;

    /**
     * (optional) This is a URL to the control panel for editing DNS at the DNS Provider.
     * This field allows a Service Provider whose template isn’t supported at the DNS Provider to provide
     * a direct link to perform manual edits.
     *
     * To allow deep links to the specific domain, this string may contain %domain% which must be replaced with
     * the domain name.
     */
    private ?string $urlControlPanel = null;

    /**
     * (optional) This is the list of nameservers desired by the DNS Provider for the zone to be authoritative.
     * This does not indicate the authoritative nameservers; for this the registry would be queried.
     */
    private array $nameServers = [];

    private bool $redirectSupported = false;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @throws \JsonException
     */
    public static function loadFromJson(string $json, string $domain): self
    {
        $result = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $obj = new self($domain);
        $ref = new \ReflectionClass($obj);

        foreach ($result as $key => $val) {
            if ($ref->hasProperty($key)) {
                $prop = $ref->getProperty($key);
                $prop->setAccessible(true);
                $prop->setValue($obj, $val);
            }
        }

        return $obj;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getUrlAPI(): string
    {
        return $this->urlAPI;
    }

    public function getProviderDisplayName(): ?string
    {
        return $this->providerDisplayName;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUrlSyncUX(): ?string
    {
        return $this->urlSyncUX;
    }

    public function getUrlAsyncUX(): ?string
    {
        return $this->urlAsyncUX;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getUrlControlPanel(): ?string
    {
        return $this->urlControlPanel;
    }

    public function getNameServers(): array
    {
        return $this->nameServers;
    }

    public function isRedirectSupported(): bool
    {
        return $this->redirectSupported;
    }
}
