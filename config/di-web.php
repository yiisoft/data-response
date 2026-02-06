<?php

declare(strict_types=1);

use Yiisoft\DataResponse\DataResponseFactory as DeprecatedDataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface as DeprecatedDataResponseFactoryInterface;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Formatter\XmlFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiator;
use Yiisoft\DataResponse\Middleware\ContentNegotiatorDataResponseMiddleware;
use Yiisoft\DataResponse\NotAcceptableRequestHandler;
use Yiisoft\DataResponse\ResponseFactory\ContentNegotiatorResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactoryInterface;
use Yiisoft\DataResponse\ResponseFactory\HtmlResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\JsonResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\XmlResponseFactory;
use Yiisoft\Definitions\DynamicReferencesArray;
use Yiisoft\Definitions\Reference;
use Yiisoft\Definitions\ReferencesArray;

/* @var $params array */

return [
    DataResponseFormatterInterface::class => HtmlDataResponseFormatter::class,
    DeprecatedDataResponseFactoryInterface::class => DeprecatedDataResponseFactory::class,
    ContentNegotiator::class => [
        '__construct()' => [
            'contentFormatters' => DynamicReferencesArray::from($params['yiisoft/data-response']['contentFormatters']),
        ],
    ],
    DataResponseFactoryInterface::class => DataResponseFactory::class,
    ContentNegotiatorDataResponseMiddleware::class => [
        '__construct()' => [
            'formatters' => ReferencesArray::from([
                'text/html' => HtmlFormatter::class,
                'application/xml' => XmlFormatter::class,
                'application/json' => JsonFormatter::class,
            ]),
            'fallback' => Reference::to(NotAcceptableRequestHandler::class),
        ],
    ],
    ContentNegotiatorResponseFactory::class => [
        '__construct()' => [
            'factories' => DynamicReferencesArray::from([
                'text/html' => HtmlResponseFactory::class,
                'application/xml' => XmlResponseFactory::class,
                'application/json' => JsonResponseFactory::class,
            ]),
            'fallback' => Reference::to(NotAcceptableRequestHandler::class),
        ],
    ],
];
