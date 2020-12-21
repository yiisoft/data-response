<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px">
    </a>
    <h1 align="center">Data response</h1>
    <br>
</p>

The package allows responding with data that is automatically converted into PSR-7 response.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/data-response/v/stable.png)](https://packagist.org/packages/yiisoft/data-response)
[![Total Downloads](https://poser.pugx.org/yiisoft/data-response/downloads.png)](https://packagist.org/packages/yiisoft/data-response)
[![Build status](https://github.com/yiisoft/data-response/workflows/build/badge.svg)](https://github.com/yiisoft/data-response/actions?query=workflow%3Abuild)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiisoft/data-response/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data-response/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/yiisoft/data-response/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiisoft/data-response/?branch=master)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fdata-response%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/data-response/master)
[![static analysis](https://github.com/yiisoft/data-response/workflows/static%20analysis/badge.svg)](https://github.com/yiisoft/data-response/actions?query=workflow%3A%22static+analysis%22)
[![type-coverage](https://shepherd.dev/github/yiisoft/data-response/coverage.svg)](https://shepherd.dev/github/yiisoft/data-response)

## General usage

The package provides `DataResponseFactory` class that, given a PSR-17 response factory, is able to create data response. In this example we use `nyholm/psr7` pacakge but any PSR-17 response factory would do.

Data response contains raw data to be processed later.

```php
use Nyholm\Psr7\Factory\Psr17Factory;
use Yiisoft\DataResponse\DataResponseFactory;

$factory = new DataResponseFactory(new Psr17Factory());
$dataResponse = $factory->createResponse('test');
$dataResponse->getBody()->rewind();

echo $dataResponse->getBody()->getContents(); //test
```

### Formatters

Formatter purpose if to format data response. In the following example we format data as JSON.

```php
use Nyholm\Psr7\Factory\Psr17Factory;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;

$factory = new DataResponseFactory(new Psr17Factory());
$dataResponse = $factory->createResponse('test');
$dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
$dataResponse->getBody()->rewind();

echo $dataResponse->getHeader('Content-Type'); //application/json
echo $dataResponse->getBody()->getContents(); //"test"
```

The following formatters are available:
* HtmlDataResponseFormatter
* JsonDataResponseFormatter
* XmlDataResponseFormatter

### Middleware

The package provides a PSR-15 middleware that is able to format a data response.

```php

use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;

$middleware = (new FormatDataResponse(new JsonDataResponseFormatter()));
//$middleware->process($request, $handler);
```

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
./vendor/bin/phpunit
```

### Mutation testing

The package tests are checked with [Infection](https://infection.github.io/) mutation framework. To run it:

```shell
./vendor/bin/infection
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
./vendor/bin/psalm
```

### Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

### Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)

## License

The Data response is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).
