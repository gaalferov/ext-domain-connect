<?php

namespace DomainConnect\DTO;

/**
 * Class DomainSettings
 */
class DomainSettings
{
    /**
     * Unique identifier for the DNS Provider.
     * To ensure non-coordinated uniqueness, this should be the domain name of the DNS Provider (e.g. virtucom.com).
     *
     * @var string
     */
    public $providerId;

    /**
     * The name of the DNS Provider.
     *
     * @var string
     */
    public $providerName;

    /**
     * The URL Prefix for the REST API
     *
     * @var string
     */
    public $urlAPI;

    /**
     * (optional) The name of the DNS Provider that should be displayed by the Service Provider.
     * This may change per domain for some DNS Providers that power multiple brands.
     *
     * @var string|null
     */
    public $providerDisplayName;

    /**
     * Root Domain Name
     *
     * @var string
     */
    public $domain;

    /**
     * (optional) Sub domain
     *
     * @var string
     */
    public $host;

    /**
     * (optional) The URL Prefix for linking to the UX of Domain Connect for the synchronous flow at the DNS Provider.
     * If not returned, the DNS Provider is not supporting the synchronous flow on this domain.
     *
     * @var string|null
     */
    public $urlSyncUX;

    /**
     * (optional) The URL Prefix for linking to the UX elements of Domain Connect for the asynchronous
     * flow at the DNS Provider.
     * If not returned, the DNS Provider is not supporting the asynchronous flow on this domain.
     *
     * @var string|null
     */
    public $urlAsyncUX;

    /**
     * (optional) This is the desired width of the window for granting consent when navigated in a popup.
     * Default value if not returned should be 750px.
     *
     * @var int
     */
    public $width = 750;

    /**
     * (optional) This is the desired height of the window for granting consent when navigated in a popup.
     * Default value if not returned should be 750px.
     *
     * @var int
     */
    public $height = 750;

    /**
     * (optional) This is a URL to the control panel for editing DNS at the DNS Provider.
     * This field allows a Service Provider whose template isn’t supported at the DNS Provider to provide
     * a direct link to perform manual edits.
     *
     * To allow deep links to the specific domain, this string may contain %domain% which must be replaced with
     * the domain name.
     *
     * @var string|null
     */
    public $urlControlPanel;

    /**
     * (optional) This is the list of nameservers desired by the DNS Provider for the zone to be authoritative.
     * This does not indicate the authoritative nameservers; for this the registry would be queried.
     *
     * @var array
     */
    public $nameServers = [];

    /**
     * @var bool
     */
    public $redirectSupported;

    /**
     * @param string $json
     *
     * @return DomainSettings
     */
    public static function loadFromJson($json)
    {
        $result = json_decode($json, true);
        $obj = new self();

        foreach (get_object_vars($obj) as $key => $val) {
            if (!empty($result[$key])) {
                $obj->{$key} = $result[$key];
            }
        }

        return $obj;
    }
}
