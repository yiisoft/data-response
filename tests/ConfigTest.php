<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\DataResponse\Middleware\ContentNegotiator;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;

final class ConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testDiWeb(): void
    {
        $container = $this->createContainer('web');

        $dataResponseFormatter = $container->get(DataResponseFormatterInterface::class);
        $dataResponseFactory = $container->get(DataResponseFactoryInterface::class);
        $contentNegotiator = $container->get(ContentNegotiator::class);

        $this->assertInstanceOf(HtmlDataResponseFormatter::class, $dataResponseFormatter);
        $this->assertInstanceOf(DataResponseFactory::class, $dataResponseFactory);
        $this->assertInstanceOf(ContentNegotiator::class, $contentNegotiator);
    }

    private function createContainer(?string $postfix = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getDiConfig($postfix)
                +
                [
                    ResponseFactoryInterface::class => $this->createMock(ResponseFactoryInterface::class),
                    StreamFactoryInterface::class => $this->createMock(StreamFactoryInterface::class),
                ]
            )
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
