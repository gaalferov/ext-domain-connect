<?php

namespace Tests\Services;

use DomainConnect\Services\TemplateService;

class TemplateServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider templateSupportSuccessProvider
     *
     * @param string $domain
     * @param string $providerId
     * @param string $serviceId
     */
    public function testGetTemplateSyncUrlSuccessCase($domain, $providerId, $serviceId)
    {
        $config = $this->configs[$domain];
        $params = [
            'randomtext' => 'shm:1531371203:Hello world sync',
            'ip' => '132.148.25.185',
        ];
        $templateUrl = self::$templateService->getTemplateSyncUrl($domain, $providerId, $serviceId, $params);

        $this->assertEquals(
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
     * @expectedException DomainConnect\Exception\TemplateNotSupportedException
     *
     * @param string $domain
     * @param string $providerId
     */
    public function testGetTemplateSyncUrlInvalidCase($domain, $providerId)
    {
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
    public function testIsTemplateSupportedSuccessCase($domain, $providerId, $serviceId)
    {
        $this->assertTrue(self::$templateService->isTemplateSupported(
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
    public function testIsTemplateSupportedInvalidCase($domain, $providerId)
    {
        $this->assertFalse(self::$templateService->isTemplateSupported(
            $providerId,
            'notExistServiceId',
            self::$dnsService->getDomainSettings($domain)
        ));
    }

    /**
     * @return array
     */
    public function templateSupportSuccessProvider()
    {
        $data = [];

        foreach ($this->configs as $domainUrl => $domainConfig) {
            $data[] = [
                $domainUrl,
                'exampleservice.domainconnect.org',
                'template1'
            ];
        }

        return $data;
    }

    /**
     * @param $params
     * @param $config
     *
     * @return string
     */
    private function getApplyQueryByParams($params, $config)
    {
        $result = array_merge([
            'domain' => $config['domain']
        ], $params);

        if (!empty($config['host'])) {
            $result['host'] = $config['host'];
        }

        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);

        return http_build_query($result);
    }
}
