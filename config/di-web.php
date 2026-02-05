<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
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
    ContentNegotiatorDataResponseMiddleware::class
        => static function (ContainerInterface $container): ContentNegotiatorDataResponseMiddleware {
            return new ContentNegotiatorDataResponseMiddleware(
                [
                    'text/html' => $container->get(HtmlFormatter::class),
                    'application/xml' => $container->get(XmlFormatter::class),
                    'application/json' => $container->get(JsonFormatter::class),
                ],
                fallback: $container->get(NotAcceptableRequestHandler::class),
            );
        },
    ContentNegotiatorResponseFactory::class
        => static function (ContainerInterface $container): ContentNegotiatorResponseFactory {
            return new ContentNegotiatorResponseFactory(
                [
                    'text/html' => $container->get(HtmlResponseFactory::class),
                    'application/xml' => $container->get(XmlResponseFactory::class),
                    'application/json' => $container->get(JsonResponseFactory::class),
                ],
                $container->get(NotAcceptableRequestHandler::class),
            );
        },
];
