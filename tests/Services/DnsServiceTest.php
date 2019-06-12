<?php

namespace Tests\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Services\DnsService;
use GuzzleHttp\Client;
use LayerShifter\TLDExtract\Extract;

class DnsServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider dnsServiceSuccessProvider
     *
     * @param DnsService $dnsService
     * @param string     $configKey
     */
    public function testGetDomainSettingsSuccessCase(DnsService $dnsService, $configKey)
    {
        $domainSettings = $dnsService->getDomainSettings();
        $config = $this->configs[$configKey];

        $this->assertInstanceOf(DomainSettings::class, $domainSettings);

        foreach ($config as $key => $value) {
            $this->assertEquals($value, $domainSettings->{$key});
        }
    }

    /**
     * @expectedException     DomainConnect\Exception\NoDomainConnectRecordException
     */
    public function testGetDomainSettingsInvalidCase()
    {
        $dnsService = new DnsService(new Client(), (new Extract())->parse('blasdasdawsdasdx.qqqqqqq'));
        $dnsService->getDomainSettings();
    }

    /**
     * @return array
     */
    public function dnsServiceSuccessProvider()
    {
        $data = [];
        $client = new Client();

        foreach ($this->configs as $domainUrl => $domainConfig) {
            $data[] = [
                new DnsService($client, (new Extract())->parse($domainUrl)),
                $domainUrl
            ];
        }

        return $data;
    }
}
