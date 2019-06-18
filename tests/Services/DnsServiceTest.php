<?php

namespace Tests\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Services\DnsService;

class DnsServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider dnsServiceSuccessProvider
     *
     * @param string     $domainUrl
     */
    public function testGetDomainSettingsSuccessCase($domainUrl)
    {
        $domainSettings = (new DnsService())->getDomainSettings($domainUrl);
        $config = $this->configs[$domainUrl];

        $this->assertInstanceOf(DomainSettings::class, $domainSettings);

        foreach ($config as $key => $value) {
            $this->assertEquals($value, $domainSettings->{$key});
        }
    }

    /**
     * @dataProvider invalidDomainProvider
     *
     * @expectedException     DomainConnect\Exception\InvalidDomainException
     */
    public function testGetDomainSettingsInvalidDomainCase($domain)
    {
        (new DnsService())->getDomainSettings($domain);
    }

    /**
     * @expectedException     DomainConnect\Exception\NoDomainConnectRecordException
     */
    public function testGetDomainSettingsInvalidCase()
    {
        (new DnsService())->getDomainSettings('blasdasdawsdasdx.qqqqqqq');
    }

    /**
     * @return array
     */
    public function dnsServiceSuccessProvider()
    {
        $data = [];

        foreach ($this->configs as $domainUrl => $domainConfig) {
            $data[] = [$domainUrl];
        }

        return $data;
    }

    /**
     * @return array
     */
    public function invalidDomainProvider()
    {
        return [
            ['http://a-.bc.com'],
            ['http://a.bc.com-'],
            ['http://toolongtoolongtoolongtoolongtoolongtoolongtoolongtoolongtoolongtoolong.com'],
        ];
    }
}
