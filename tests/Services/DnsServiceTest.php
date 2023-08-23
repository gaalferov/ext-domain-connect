<?php

declare(strict_types=1);

namespace Tests\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainException;
use DomainConnect\Exception\NoDomainConnectRecordException;

class DnsServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider dnsServiceSuccessProvider
     *
     * @param string     $domainUrl
     */
    public function testGetDomainSettingsSuccessCase(string $domainUrl): void
    {
        $domainSettings = self::$dnsService->getDomainSettings($domainUrl);
        $config = $this->configs[$domainUrl];

        $this->assertInstanceOf(DomainSettings::class, $domainSettings);

        foreach ($config as $key => $value) {
            $methodName = 'get' . ucfirst($key);
            $this->assertEquals($value, $domainSettings->$methodName());
        }
    }

    /**
     * @dataProvider invalidDomainProvider
     */
    public function testGetDomainSettingsInvalidDomainCase($domain): void
    {
        $this->expectException(InvalidDomainException::class);

        self::$dnsService->getDomainSettings($domain);
    }

    public function testGetDomainSettingsInvalidCase(): void
    {
        $this->expectException(NoDomainConnectRecordException::class);

        self::$dnsService->getDomainSettings('blasdasdawsdasdx.qqqqqqq');
    }

    /**
     * @return array
     */
    public function dnsServiceSuccessProvider(): array
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
    public function invalidDomainProvider(): array
    {
        return [
            ['a-.bc.com'],
            ['toolongtoolongtoolongtoolongtoolongtoolongtoolongtoolongtoolongtoolong.com'],
        ];
    }
}
