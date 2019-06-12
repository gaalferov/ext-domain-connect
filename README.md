# The Domain Connect Extension

Buying a domain name is just the start of a journey. To put your website online, you need to associate it with your domain.

If you have bought a domain name from a registrar and host your website at another hosting provider, you may need to point the domain name to your Plesk server. If your domain name is already pointing to Plesk, you may want to add additional third-party services (for example, mail or e-commerce platform) to your website.  

In both cases, you will have to configure DNS settings. This may be a challenging task, especially for a beginner. To accomplish this task easily, use the Domain Connect extension. Just provide the domain name, and the extension will automatically configure DNS settings for you.

# domain-connect
PHP client library for Domain Connect protocol.
For details of the protocol, please visit: https://domainconnect.org

## Specification reference
https://github.com/Domain-Connect/spec/blob/master/Domain%20Connect%20Spec%20Draft.adoc
- Version: 2.1
- Revision: 52

## Usage

### Sync flow

Just get the link. Discovery and template query part is solved automatically.
```php

require 'vendor/autoload.php';

use DomainConnect\DomainConnect;
use DomainConnect\Exception\DomainConnectException;
use GuzzleHttp\Client;

try {
    $domainConnect = new DomainConnect(new Client(), 'https://demo.bblog.online');
    $applyUrl = $domainConnect->templateService->getTemplateSyncUrl(
        'exampleservice.domainconnect.org',
        'template1',
        [
            'randomtext' => 'shm:1531371203:Hello world sync',
            'ip' => '132.148.25.185',
            'redirect_uri' => 'https://google.com',
            'groupId' => '1,2,3'
        ]
    );

    print_r($applyUrl);
    print_r($domainConnect->templateService->domainSettings);
} catch (DomainConnectException $e) {
    echo (sprintf('An error has occurred: %s', $e->getMessage()));
}
```

Output:
```text
https://dcc.godaddy.com/manage/v2/domainTemplates/providers/exampleservice.domainconnect.org/services/template1/apply?domain=bblog.online&groupId=1%2C2%2C3&host=demo&ip=132.148.25.185&providerName=GoDaddy&randomtext=shm%3A1531371203%3AHello+world+sync&redirect_uri=https%3A%2F%2Fgoogle.comDomainConnect\DTO\DomainSettings Object
(
    [providerId] => 
    [providerName] => GoDaddy
    [urlAPI] => https://domainconnect.api.godaddy.com
    [providerDisplayName] => 
    [domain] => bblog.online
    [urlSyncUX] => https://dcc.godaddy.com/manage
    [urlAsyncUX] => https://dcc.godaddy.com/manage
    [width] => 750
    [height] => 750
    [urlControlPanel] => https://dcc.godaddy.com/manage/dns
    [nameServers] => Array
        (
        )

    [redirectSupported] => 
)
```

## Custom http/https proxy

```php
...
$domainConnect = new DomainConnect(
    new Client([
    'proxy' => [
        'http'  => 'tcp://localhost:8125', // Use this proxy with "http"
        'https' => 'tcp://localhost:9124', // Use this proxy with "https",
        'no' => ['.mit.edu', 'foo.com']    // Don't use a proxy with these
    ]]),
    'https://demo.bblog.online'
);
...
```

## Tests
To run tests use next command: `composer tests` or `php vendor/bin/phpunit ./tests`

## TODOs
- Async flow
- Sync flow with signed request
