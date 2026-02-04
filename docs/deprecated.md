# Deprecated

> [!WARNING]
> The classes described in this document are deprecated and will be removed in a future version.

## General usage

The package provides `DataResponseFactory` class that, given a [PSR-17](https://www.php-fig.org/psr/psr-17/)
response factory, is able to create data response.

Data response contains raw data to be processed later.

```php
use Yiisoft\DataResponse\DataResponseFactory;

/**
 * @var Psr\Http\Message\ResponseFactoryInterface $responseFactory
 */

$factory = new DataResponseFactory($responseFactory);
$dataResponse = $factory->createResponse('test');
$dataResponse
    ->getBody()
    ->rewind();

echo $dataResponse
    ->getBody()
    ->getContents(); // "test"
```

## Formatters

Formatter purpose is to format a data response. In the following example we format data as JSON.

```php
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;

/**
 * @var Psr\Http\Message\ResponseFactoryInterface $responseFactory
 */

$factory = new DataResponseFactory($responseFactory);
$dataResponse = $factory->createResponse('test');
$dataResponse = $dataResponse->withResponseFormatter(new JsonDataResponseFormatter());
$dataResponse
    ->getBody()
    ->rewind();

echo $dataResponse->getHeader('Content-Type'); // ["application/json; charset=UTF-8"]
echo $dataResponse
    ->getBody()
    ->getContents(); // "test"
```

The following formatters are available:

- `HtmlDataResponseFormatter`
- `JsonDataResponseFormatter`
- `XmlDataResponseFormatter`
- `PlainTextDataResponseFormatter`

## Middleware

The package provides a [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware that is able to format a data response.

```php
use Yiisoft\DataResponse\Middleware\FormatDataResponse;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;

$middleware = (new FormatDataResponse(new JsonDataResponseFormatter()));
//$middleware->process($request, $handler);
```

Also, the package provides [PSR-15](https://www.php-fig.org/psr/psr-15/) middleware for content negotiation:

```php
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\XmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiator;

$middleware = new ContentNegotiator([
    'text/html' => new HtmlDataResponseFormatter(),
    'application/xml' => new XmlDataResponseFormatter(),
    'application/json' => new JsonDataResponseFormatter(),
]);
```

You can override middlewares with method `withContentFormatters()`:

```php
$middleware->withContentFormatters([
    'application/xml' => new XmlDataResponseFormatter(),
    'application/json' => new JsonDataResponseFormatter(),
]);
```
