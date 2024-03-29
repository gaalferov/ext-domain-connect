<?php

declare(strict_types=1);

namespace Tests\Services;

use DomainConnect\Services\DnsService;
use DomainConnect\Services\TemplateService;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseServiceTest.
 */
abstract class BaseServiceTest extends TestCase
{
    public const CONFIGS = [
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

    protected static ?Client $client;

    protected static ?DnsService $dnsService;

    protected static ?TemplateService $templateService;

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
