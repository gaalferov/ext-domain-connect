<?php

declare(strict_types=1);

namespace Tests\Services;

use DomainConnect\DTO\DomainSettings;
use DomainConnect\Exception\InvalidDomainException;
use DomainConnect\Exception\NoDomainConnectRecordException;

/**
 * @internal
 *
 * @covers \DomainConnect\Services\DnsService
 */
final class DnsServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider provideGetDomainSettingsSuccessCaseCases
     */
    public function testGetDomainSettingsSuccessCase(string $domainUrl): void
    {
        $domainSettings = self::$dnsService->getDomainSettings($domainUrl);
        $config = BaseServiceTest::CONFIGS[$domainUrl];

        self::assertInstanceOf(DomainSettings::class, $domainSettings);

        foreach ($config as $key => $value) {
            $methodName = 'get'.ucfirst($key);
            self::assertSame($value, $domainSettings->{$methodName}());
        }
    }

    /**
     * @dataProvider provideGetDomainSettingsInvalidDomainCaseCases
     */
    public function testGetDomainSettingsInvalidDomainCase(string $domain): void
    {
        $this->expectException(InvalidDomainException::class);

        self::$dnsService->getDomainSettings($domain);
    }

    public function testGetDomainSettingsInvalidCase(): void
    {
        $this->expectException(NoDomainConnectRecordException::class);

        self::$dnsService->getDomainSettings('blasdasdawsdasdx.qqqqqqq');
    }

    public static function provideGetDomainSettingsSuccessCaseCases(): iterable
    {
        $data = [];

        foreach (BaseServiceTest::CONFIGS as $domainUrl => $domainConfig) {
            $data[] = [$domainUrl];
        }

        return $data;
    }

    public static function provideGetDomainSettingsInvalidDomainCaseCases(): iterable
    {
        return [
            ['a-.bc.com'],
            ['toolongtoolongtoolongtoolongtoolongtoolongtoolongtoolongtoolongtoolong.com'],
        ];
    }
}
