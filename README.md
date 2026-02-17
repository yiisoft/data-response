<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://yiisoft.github.io/docs/images/yii_logo.svg" height="100px" alt="Yii">
    </a>
    <h1 align="center">Yii Data Response</h1>
    <br>
</p>

[![Latest Stable Version](https://poser.pugx.org/yiisoft/data-response/v)](https://packagist.org/packages/yiisoft/data-response)
[![Total Downloads](https://poser.pugx.org/yiisoft/data-response/downloads)](https://packagist.org/packages/yiisoft/data-response)
[![Build status](https://github.com/yiisoft/data-response/actions/workflows/build.yml/badge.svg)](https://github.com/yiisoft/data-response/actions/workflows/build.yml)
[![Code Coverage](https://codecov.io/gh/yiisoft/data-response/graph/badge.svg?token=SPEX4FPFBU)](https://codecov.io/gh/yiisoft/data-response)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fyiisoft%2Fdata-response%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/yiisoft/data-response/master)
[![Static analysis](https://github.com/yiisoft/data-response/actions/workflows/static.yml/badge.svg?branch=master)](https://github.com/yiisoft/data-response/actions/workflows/static.yml?query=branch%3Amaster)
[![type-coverage](https://shepherd.dev/github/yiisoft/data-response/coverage.svg)](https://shepherd.dev/github/yiisoft/data-response)

The package allows responding with data that is automatically converted into [PSR-7](https://www.php-fig.org/psr/psr-7/)
response.

## Requirements

- PHP 8.1 - 8.5.
- `DOM` PHP extension.

## Installation

The package could be installed with [Composer](https://getcomposer.org):

```shell
composer require yiisoft/data-response
```

## General usage

### Response Factories

The package provides response factories that create [PSR-7](https://www.php-fig.org/psr/psr-7/) responses
with `DataStream` body. The data is formatted lazily when the response body is read.

```php
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\DataResponse\Formatter\JsonFormatter;

/**
 * @var Psr\Http\Message\ResponseFactoryInterface $responseFactory
 */

$factory = new JsonResponseFactory($responseFactory, new JsonFormatter());
$response = $factory->createResponse(['key' => 'value']);

$response->getBody()->rewind();
echo $response->getBody()->getContents(); // {"key":"value"}
echo $response->getHeaderLine('Content-Type'); // application/json; charset=UTF-8
```

The following response factories are available:

- `JsonResponseFactory` — creates responses with JSON-formatted body;
- `XmlResponseFactory` — creates responses with XML-formatted body;
- `HtmlResponseFactory` — creates responses with HTML-formatted body;
- `PlainTextResponseFactory` — creates responses with plain text body;
- `DataResponseFactory` — creates responses without a predefined formatter, use middleware to format.

### Middleware

The package provides [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware that formats `DataStream` responses
without a predefined formatter.

```php
use Yiisoft\DataResponse\Middleware\JsonDataResponseMiddleware;
use Yiisoft\DataResponse\Formatter\JsonFormatter;

$middleware = new JsonDataResponseMiddleware(new JsonFormatter());
```

The following middleware are available:

- `HtmlDataResponseMiddleware`
- `JsonDataResponseMiddleware`
- `XmlDataResponseMiddleware`
- `PlainTextDataResponseMiddleware`

### Content Negotiation

The package provides content negotiation via middleware and response factory.

#### Middleware

`ContentNegotiatorDataResponseMiddleware` selects a formatter based on the request's `Accept` header:

```php
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\DataResponse\Formatter\XmlFormatter;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiatorDataResponseMiddleware;

$middleware = new ContentNegotiatorDataResponseMiddleware(
    formatters: [
        'text/html' => new HtmlFormatter(),
        'application/xml' => new XmlFormatter(),
        'application/json' => new JsonFormatter(),
    ],
    fallback: new JsonFormatter(),
);
```

The `fallback` parameter also accepts a `RequestHandlerInterface`, for example `NotAcceptableRequestHandler`
to return a 406 response when no formatter matches.

#### Response Factory

`ContentNegotiatorResponseFactory` selects a response factory based on the request's `Accept` header:

```php
use Yiisoft\DataResponse\ResponseFactory\ContentNegotiatorResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\XmlResponseFactory;

/**
 * @var JsonResponseFactory $jsonResponseFactory
 * @var XmlResponseFactory $xmlResponseFactory
 */

$factory = new ContentNegotiatorResponseFactory(
    factories: [
        'application/json' => $jsonResponseFactory,
        'application/xml' => $xmlResponseFactory,
    ],
    fallback: $jsonResponseFactory,
);

$response = $factory->createResponse($request, ['key' => 'value']);
```

The `fallback` parameter also accepts a `RequestHandlerInterface`, for example `NotAcceptableRequestHandler`
to return a 406 response when no factory matches.

### DataStream

`DataStream` is a [PSR-7](https://www.php-fig.org/psr/psr-7/) stream that lazily formats data.
It wraps raw data and a formatter, and performs formatting only when the stream is read.
A formatter is optional at construction time, but must be set before reading the stream,
otherwise a `LogicException` will be thrown.

```php
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Formatter\XmlFormatter;

$stream = new DataStream(['key' => 'value'], new JsonFormatter());

echo (string) $stream; // {"key":"value"}

// You can change the data or formatter dynamically
$stream->changeData(['new' => 'data']);
$stream->changeFormatter(new XmlFormatter());
```

## Documentation

- [Deprecated classes](docs/deprecated.md)
- [Internals](docs/internals.md)

If you need help or have a question, the [Yii Forum](https://forum.yiiframework.com/c/yii-3-0/63) is a good place for that.
You may also check out other [Yii Community Resources](https://www.yiiframework.com/community).

## License

The Yii Data Response is free software. It is released under the terms of the BSD License.
Please see [`LICENSE`](./LICENSE.md) for more information.

Maintained by [Yii Software](https://www.yiiframework.com/).

## Support the project

[![Open Collective](https://img.shields.io/badge/Open%20Collective-sponsor-7eadf1?logo=open%20collective&logoColor=7eadf1&labelColor=555555)](https://opencollective.com/yiisoft)

## Follow updates

[![Official website](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](https://www.yiiframework.com/)
[![Twitter](https://img.shields.io/badge/twitter-follow-1DA1F2?logo=twitter&logoColor=1DA1F2&labelColor=555555?style=flat)](https://twitter.com/yiiframework)
[![Telegram](https://img.shields.io/badge/telegram-join-1DA1F2?style=flat&logo=telegram)](https://t.me/yii3en)
[![Facebook](https://img.shields.io/badge/facebook-join-1DA1F2?style=flat&logo=facebook&logoColor=ffffff)](https://www.facebook.com/groups/yiitalk)
[![Slack](https://img.shields.io/badge/slack-join-1DA1F2?style=flat&logo=slack)](https://yiiframework.com/go/slack)
