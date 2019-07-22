<?php

namespace Tests\Services;

use DomainConnect\Services\DnsService;
use DomainConnect\Services\TemplateService;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseServiceTest
 */
abstract class BaseServiceTest extends TestCase
{
    /**
     * @var Client
     */
    protected static $client;

    /**
     * @var DnsService
     */
    protected static $dnsService;

    /**
     * @var TemplateService
     */
    protected static $templateService;

    /**
     * @var array
     */
    public $configs = [
        'connect.domains' => [
            'providerName' => '1and1',
            'urlAPI' => 'https://api.domainconnect.1and1.com',
            'domain' => 'connect.domains',
            'urlSyncUX' => 'https://domainconnect.1and1.com/sync',
            'urlAsyncUX' => 'https://domainconnect.1and1.com/async',
        ],
        'http://subdomain.weathernyc.nyc' => [
            'providerName' => 'GoDaddy',
            'urlAPI' => 'https://domainconnect.api.godaddy.com',
            'domain' => 'weathernyc.nyc',
            'host' => 'subdomain',
            'urlSyncUX' => 'https://dcc.godaddy.com/manage',
            'urlAsyncUX' => 'https://dcc.godaddy.com/manage',
        ],
    ];

    public static function setUpBeforeClass()
    {
        self::$client = new Client(['verify' => false]);
        self::$dnsService = new DnsService(self::$client);
        self::$templateService = new TemplateService();

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        self::$client = null;
        self::$dnsService = null;
        self::$templateService = null;

        parent::tearDownAfterClass();
    }
}
