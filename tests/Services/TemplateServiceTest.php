<?php

namespace Tests\Services;

use DomainConnect\Services\DnsService;
use DomainConnect\Services\TemplateService;
use GuzzleHttp\Client;
use LayerShifter\TLDExtract\Extract;

class TemplateServiceTest extends BaseServiceTest
{
    /**
     * @dataProvider templateSupportSuccessProvider
     *
     * @param TemplateService $templateService
     * @param string          $providerId
     * @param string          $serviceId
     * @param string          $configKey
     */
    public function testGetTemplateSyncUrlSuccessCase($templateService, $providerId, $serviceId, $configKey)
    {
        $config = $this->configs[$configKey];
        $params = [
            'randomtext' => 'shm:1531371203:Hello world sync',
            'ip' => '132.148.25.185',
        ];

        $templateUrl = $templateService->getTemplateSyncUrl($providerId, $serviceId, $params);

        $this->assertEquals(
            sprintf(
                $templateService::TEMPLATE_APPLY_URL,
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
     * @param TemplateService $templateService
     * @param string          $providerId
     */
    public function testGetTemplateSyncUrlInvalidCase($templateService, $providerId)
    {
        $templateService->getTemplateSyncUrl(
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
     * @param TemplateService $templateService
     * @param string          $providerId
     * @param string          $serviceId
     */
    public function testIsTemplateSupportedSuccessCase(TemplateService $templateService, $providerId, $serviceId)
    {
        $this->assertTrue($templateService->isTemplateSupported($providerId, $serviceId));
    }

    /**
     * @dataProvider templateSupportSuccessProvider
     *
     * @param TemplateService $templateService
     * @param string                $providerId
     */
    public function testIsTemplateSupportedInvalidCase(TemplateService $templateService, $providerId)
    {
        $this->assertFalse($templateService->isTemplateSupported($providerId, 'notExistServiceId'));
    }

    /**
     * @return array
     */
    public function templateSupportSuccessProvider()
    {
        $data = [];
        $client = new Client();

        foreach ($this->configs as $domainUrl => $domainConfig) {
            $resultDomainInfo = (new Extract())->parse($domainUrl);

            $data[] = [
                new TemplateService(
                    $client,
                    $resultDomainInfo,
                    (new DnsService($client, $resultDomainInfo))->getDomainSettings()
                ),
                'exampleservice.domainconnect.org',
                'template1',
                $domainUrl
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
            'domain' => $config['domain'],
            'providerName' => $config['providerName'],
        ], $params);

        ksort($result, SORT_NATURAL | SORT_FLAG_CASE);

        return http_build_query($result);
    }
}
