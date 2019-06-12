<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;

/**
 * Class BaseServiceTest
 */
abstract class BaseServiceTest extends TestCase
{
    /**
     * @var array
     */
    public $configs = [
        'www.connect.domains' => [
            'providerName' => '1and1',
            'urlAPI' => 'https://api.domainconnect.1and1.com',
            'domain' => 'connect.domains',
            'urlSyncUX' => 'https://domainconnect.1and1.com/sync',
            'urlAsyncUX' => 'https://domainconnect.1and1.com/async',
        ],
        'http://www.weathernyc.nyc' => [
            'providerName' => 'GoDaddy',
            'urlAPI' => 'https://domainconnect.api.godaddy.com',
            'domain' => 'weathernyc.nyc',
            'urlSyncUX' => 'https://dcc.godaddy.com/manage',
            'urlAsyncUX' => 'https://dcc.godaddy.com/manage',
        ],
    ];
}
