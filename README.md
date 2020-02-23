[![Codacy Badge](https://api.codacy.com/project/badge/Grade/032df50a272e4fc1a06576d68130066a)](https://app.codacy.com/app/gaalferov/ext-domain-connect?utm_source=github.com&utm_medium=referral&utm_content=gaalferov/ext-domain-connect&utm_campaign=Badge_Grade_Dashboard)
[![Build Status](https://travis-ci.com/gaalferov/ext-domain-connect.svg?branch=master)](https://travis-ci.com/gaalferov/ext-domain-connect)

# domain-connect
PHP client library for Domain Connect protocol.
For details of the protocol, please visit: https://domainconnect.org
Library offers Service Provider functionality in Sync mode.

## Specification reference
https://github.com/Domain-Connect/spec/blob/master/Domain%20Connect%20Spec%20Draft.adoc
- Version: 2.1
- Revision: 61

## Install
```bash
composer require gaalferov/php-ext-domain-connect
```

## Usage

### Sync flow

Just get the link. Discovery and template query part is solved automatically.
```php
<?php

require 'vendor/autoload.php';

use DomainConnect\Exception\DomainConnectException;
use DomainConnect\Services\TemplateService;

try {
    $applyUrl = (new TemplateService())->getTemplateSyncUrl(
        'foo.connect.domains',
        'exampleservice.domainconnect.org',
        'template1',
        [
            'IP' => '132.148.25.185',
            'RANDOMTEXT' => 'shm:1531371203:Hello world sync',
            'redirect_uri' => 'http://example.com',
            'state' => 'someState'
        ],
        $privateKey,
        $keyId
    );

    print_r($applyUrl);
} catch (DomainConnectException $e) {
    echo (sprintf('An error has occurred: %s', $e->getMessage()));
}

```

Output:
```text
https://domainconnect.1and1.com/sync/v2/domainTemplates/providers/exampleservice.domainconnect.org/services/template1/apply?domain=connect.domains&host=foo&IP=132.148.25.185&RANDOMTEXT=shm%3A1531371203%3AHello+world+sync&redirect_uri=http%3A%2F%2Fexample.com&state=someState
```

### Sync flow with signed request

```php
<?php

require 'vendor/autoload.php';

use DomainConnect\Exception\DomainConnectException;
use DomainConnect\Services\TemplateService;

try {
    $templateService = new TemplateService();
    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\nMIIEowIBAAKCAQEA18SgvpmeasN4BHkkv0SBjAzIc4grYLjiAXRtNiBUiGUDMeTzQrKTsWvy9NuxU1dIHCZy9o1CrKNg5EzLIZLNyMfI6qiXnM+HMd4byp97zs/3D39Q8iR5poubQcRaGozWx8yQpG0OcVdmEVcTfyR/XSEWC5u16EBNvRnNAOAvZYUdWqVyQvXsjnxQot8KcK0QP8iHpoL/1dbdRy2opRPQ2FdZpovUgknybq/6FkeDtW7uCQ6Mvu4QxcUa3+WP9nYHKtgWip/eFxpeb+qLvcLHf1h0JXtxLVdyy6OLk3f2JRYUX2ZZVDvG3biTpeJz6iRzjGg6MfGxXZHjI8weDjXrJwIDAQABAoIBAGiPedJDwXg9d1i7mCo0OY8z1qPeFh9OGP/Zet8i9bQPN2gjahslTNtK07cDC8C2aFRz8Xw3Ylsk5VxdNobzjFPDNUM6JhawnvR0jQU5GhdTwoc5DHH7aRRjTP6m938sRx0VrfZwfvJAB09Z4jHX7vyjfvprH9EH8GQ2L5lACtfnsSASVJB77H1vtgxTnum74CSqIck1MCjPD/TVUtYfMJwkUQWcbk79N4nvnEoagqsDrvw4okU2OYMWucQjyxfWTU4NGlsDScRbdDAb8sLr3DpMfXM8vpZJ3Ed6gfw14hEJym8XoHwDHmjGmgYH9iG6MODxuO5TLRmRR6b+jcUV/2kCgYEA4WGsDUO/NIXIqtDm5lTi5qeFl0sGKIgRLGuCrvjLF0Fq5Yx28wuow3OhZ3rbjlmhf9nUt24nUUY67plv2pi+vx3kVdbcNfk+Wkc0wfx8+U91qaTplMRhNjrnq/Kp9E7xtnzZRInpUG1Ha5ozTYobVvklUvjodFlF2c16Zz2X2AMCgYEA9RSeZm7oMyJbe985SScXruwt5ZXlUBoBLDZAeMloPpaqknFmSVSNgtniywztF8HppJQyiMvmUOUL2tKnuShXwsvTkCTBC/vNGXutiPS8O2yqeQ8dHoHuKcoMFwgajrbPrVkuFtUkjbQJ/TKoZtrxUdCryDZ/AHmRtiHh9E4NUQ0CgYAE7ngvSh4y7gJ4Cl4jCBR26492wgN+e4u0px2S6oq3FY1bPHmV09l7fVo4w21ubfOksoV/BgACPUEo216hL9psoCDQ6ASlgbCllQ1IeVfatKxka+FYift+jkdnccXaPKf5UD4Iy+O5CMsZRaR9u9nhS05PxHaBpTpsC5z0CVr7NQKBgQCsBTzpSQ9SVNtBpvzei8Hj1YKhkwTRpG8OSUYXgcbZp4cyIsZY0jBBmA3H19rSwhjsm9icjAGs5hfcD+AJ5nczEz37/tBBSQw8xsKXTrCQRUWikyktMKWqT1cNE3MQmOBMHDxtak2t6KDaR6RMDYE0m/L3JMkf3DSaUk323JIcQQKBgD6lHhw79Cenpezzf0566uWE1QF6Sv3kWk6Gkzo2jUGmjo2tG1v2Nj82DvcTuqvfUKSr2wTKINxnKGyYXGto0BykdxeFbR04cNcBB46zUjasro2ZCvIoAHCpohNBI2dL6dI+RI3jC/KY3jPNI0toaOTWkeAvJ7w09G2ttlv8qLNV\n-----END RSA PRIVATE KEY-----";
    $keyId = '_dck1';
    
    $applyUrl = $templateService->getTemplateSyncUrl(
        'foo.connect.domains',
        'exampleservice.domainconnect.org',
        'template1',
        [
            'IP' => '132.148.25.185',
            'RANDOMTEXT' => 'shm:1531371203:Hello world sync',
            'redirect_uri' => 'http://example.com',
            'state' => 'someState'
        ],
        $privateKey,
        $keyId
    );

    print_r($applyUrl);
} catch (DomainConnectException $e) {
    echo (sprintf('An error has occurred: %s', $e->getMessage()));
}

```

Output:
```text
https://domainconnect.1and1.com/sync/v2/domainTemplates/providers/exampleservice.domainconnect.org/services/template1/apply?domain=connect.domains&host=foo&IP=132.148.25.185&RANDOMTEXT=shm%3A1531371203%3AHello+world+sync&redirect_uri=http%3A%2F%2Fexample.com&state=someState&sig=HOoCBTFNIjWK3Qjy7CtWLB9HOcVvaGYFD3XsaI%2BZm%2BHtm%2FgsK66kfEfbi7Y8uoNOAbJXx3xfnbKrdASNa%2FPjQQKLk11NV%2BLNf3iDwkg7cOV9l54L2PqHFymOgu%2BiXr3I5aUkmA%2F19YAqiz3VJrh3K1XGeqc100cyZo%2BWq90bVcyWGVhIf5vYZEsU%2FzprckhmQdipqFfAB6XB4i72RzdxCKpBUFsyWcWYX80cz873pLN42uV%2BwGFUhSQ0qDC8d60n5m1f8h9Fdmik6%2FD%2BnmPpfK4Ge%2BHGnnq%2FpvR3nb95x9DKDHOd%2BfaIVcLhIZEbbVxIa57bGH%2FRUQiMbApBqkM4YQ%3D%3D&key=_dck1
```

### Request Options (guzzle) 
```php
<?php

require 'vendor/autoload.php';

use DomainConnect\Exception\DomainConnectException;
use DomainConnect\Services\TemplateService;

try {
    $templateService = new TemplateService([
        'proxy' => [
            'http'  => 'tcp://localhost:8125',
            'https' => 'tcp://localhost:9124',
            'no' => ['.mit.edu', 'foo.com']
        ]
    ]);
    
    $applyUrl = $templateService->getTemplateSyncUrl(
        'foo.connect.domains',
        'exampleservice.domainconnect.org',
        'template1',
        [
            'IP' => '132.148.25.185',
            'RANDOMTEXT' => 'shm:1531371203:Hello world sync',
            'redirect_uri' => 'http://example.com',
            'state' => 'someState'
        ],
    );

    print_r($applyUrl);
} catch (DomainConnectException $e) {
    echo (sprintf('An error has occurred: %s', $e->getMessage()));
}

```

## Tests
To run tests use next command: `composer tests` or `php vendor/bin/phpunit ./tests`

## TODOs
- Async flow
