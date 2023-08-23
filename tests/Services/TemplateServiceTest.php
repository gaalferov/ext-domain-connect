<?php

declare(strict_types=1);

namespace Tests\Services;

use DomainConnect\Exception\TemplateNotSupportedException;
use DomainConnect\Services\TemplateService;

/**
 * @internal
 *
 * @covers \DomainConnect\Services\TemplateService
 */
final class TemplateServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider templateSupportSuccessProvider
     */
    public function testGetTemplateSyncUrlSuccessCase(string $domain, string $providerId, string $serviceId): void
    {
        $config = BaseServiceTest::CONFIGS[$domain];
        $params = [
            'randomtext' => 'shm:1531371203:Hello world sync',
            'ip' => '132.148.25.185',
        ];
        $templateUrl = self::$templateService->getTemplateSyncUrl($domain, $providerId, $serviceId, $params);

        self::assertSame(
            sprintf(
                TemplateService::TEMPLATE_APPLY_URL,
                $config['urlSyncUX'],
                $providerId,
                $serviceId,
                $this->getApplyQueryByParams($params, $config)
            ),
            $templateUrl
        );
    }

    /**
     * @dataProvider templateSupportSuccessProvider
     */
    public function testGetTemplateSyncUrlInvalidCase(string $domain, string $providerId): void
    {
        $this->expectException(TemplateNotSupportedException::class);

        self::$templateService->getTemplateSyncUrl(
            $domain,
            $providerId,
            'notExistServiceId',
            [
                'randomtext' => 'shm:1531371203:Hello world sync',
                'ip' => '132.148.25.185',
            ]
        );
    }

    /**
     * @dataProvider templateSupportSuccessProvider
     *
     * @param string $domain
     * @param string $providerId
     * @param string $serviceId
     */
    public function testIsTemplateSupportedSuccessCase($domain, $providerId, $serviceId): void
    {
        self::assertTrue(self::$templateService->isTemplateSupported(
            $providerId,
            $serviceId,
            self::$dnsService->getDomainSettings($domain)
        ));
    }

    /**
     * @dataProvider templateSupportSuccessProvider
     *
     * @param string $domain
     * @param string $providerId
     */
    public function testIsTemplateSupportedInvalidCase($domain, $providerId): void
    {
        self::assertFalse(self::$templateService->isTemplateSupported(
            $providerId,
            'notExistServiceId',
            self::$dnsService->getDomainSettings($domain)
        ));
    }

    public static function templateSupportSuccessProvider(): iterable
    {
        $data = [];

        foreach (BaseServiceTest::CONFIGS as $domainUrl => $domainConfig) {
            $data[] = [
                $domainUrl,
                'exampleservice.domainconnect.org',
                'template1',
            ];
        }

        return $data;
    }

    private function getApplyQueryByParams($params, $config): string
    {
        $result = array_merge([
            'domain' => $config['domain'],
        ], $params);

        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);

        return http_build_query($result);
    }
}
