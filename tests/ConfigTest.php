<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\DataResponse\DataResponseFactory as DeprecatedDataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface as DeprecatedDataResponseFactoryInterface;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiator;
use Yiisoft\DataResponse\Middleware\ContentNegotiatorDataResponseMiddleware;
use Yiisoft\DataResponse\ResponseFactory\ContentNegotiatorResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactory;
use Yiisoft\DataResponse\ResponseFactory\DataResponseFactoryInterface;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use PHPUnit\Framework\TestCase;

use function dirname;

final class ConfigTest extends TestCase
{
    public function testDiWeb(): void
    {
        $container = $this->createContainer('web');

        $dataResponseFactory = $container->get(DataResponseFactoryInterface::class);
        $contentNegotiatorDataResponseMiddleware = $container->get(ContentNegotiatorDataResponseMiddleware::class);
        $contentNegotiatorResponseFactory = $container->get(ContentNegotiatorResponseFactory::class);

        $this->assertInstanceOf(DataResponseFactory::class, $dataResponseFactory);
        $this->assertInstanceOf(ContentNegotiatorDataResponseMiddleware::class, $contentNegotiatorDataResponseMiddleware);
        $this->assertInstanceOf(ContentNegotiatorResponseFactory::class, $contentNegotiatorResponseFactory);
    }

    public function testDiWebDeprecated(): void
    {
        $container = $this->createContainer('web');

        $dataResponseFormatter = $container->get(DataResponseFormatterInterface::class);
        $dataResponseFactory = $container->get(DeprecatedDataResponseFactoryInterface::class);
        $contentNegotiator = $container->get(ContentNegotiator::class);

        $this->assertInstanceOf(HtmlDataResponseFormatter::class, $dataResponseFormatter);
        $this->assertInstanceOf(DeprecatedDataResponseFactory::class, $dataResponseFactory);
        $this->assertInstanceOf(ContentNegotiator::class, $contentNegotiator);
    }

    private function createContainer(?string $postfix = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($postfix)
                + [
                    ResponseFactoryInterface::class => $this->createMock(ResponseFactoryInterface::class),
                    StreamFactoryInterface::class => $this->createMock(StreamFactoryInterface::class),
                ],
            ),
        );
    }

    private function getDiConfig(?string $postfix = null): array
    {
        $params = $this->getParams();
        return require dirname(__DIR__) . '/config/di' . ($postfix !== null ? '-' . $postfix : '') . '.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
