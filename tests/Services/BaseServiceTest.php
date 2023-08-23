<?php

declare(strict_types=1);

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
     * @var Client|null
     */
    protected static ?Client $client;

    /**
     * @var DnsService|null
     */
    protected static ?DnsService $dnsService;

    /**
     * @var TemplateService|null
     */
    protected static ?TemplateService $templateService;

    /**
     * @var array
     */
    public array $configs = [
        'connect.domains' => [
            'providerName' => 'IONOS',
            'urlAPI' => 'https://api.domainconnect.ionos.com',
            'domain' => 'connect.domains',
            'urlSyncUX' => 'https://domainconnect.ionos.com/sync',
            'urlAsyncUX' => 'https://domainconnect.ionos.com/async',
        ],
        'domainconnect.org' => [
            'providerName' => 'GoDaddy',
            'urlAPI' => 'https://domainconnect.api.godaddy.com',
            'domain' => 'domainconnect.org',
            'urlSyncUX' => 'https://dcc.godaddy.com/manage',
            'urlAsyncUX' => 'https://dcc.godaddy.com/manage',
        ],
    ];

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client(['verify' => false]);
        self::$dnsService = new DnsService(self::$client);
        self::$templateService = new TemplateService();

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        self::$client = null;
        self::$dnsService = null;
        self::$templateService = null;

        parent::tearDownAfterClass();
    }
}
