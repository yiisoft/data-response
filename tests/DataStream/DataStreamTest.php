<?php

declare(strict_types=1);

namespace DataStream;

use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\FormatterInterface;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Formatter\PlainTextFormatter;

final class DataStreamTest extends TestCase
{
    public function testBase(): void
    {
        $stream = new DataStream('test data');

        $this->assertSame('test data', (string) $stream);
        $this->assertFalse($stream->hasFormatter());
    }

    public function testFormatter(): void
    {
        $stream = new DataStream('test', new JsonFormatter());

        $this->assertTrue($stream->hasFormatter());
        $this->assertSame('"test"', (string) $stream);
    }

    public function testFallbackFormatter(): void
    {
        $stream = new DataStream(
            'test',
            fallbackFormatter: new JsonFormatter()
        );

        $this->assertFalse($stream->hasFormatter());
        $this->assertSame('"test"', (string) $stream);
    }

    public function testChangeFormatter(): void
    {
        $formatter = new JsonFormatter();
        $stream = new DataStream('test');

        $stream->changeFormatter($formatter);

        $this->assertTrue($stream->hasFormatter());
        $this->assertSame('"test"', (string) $stream);
    }

    public function testChangeData(): void
    {
        $stream = new DataStream('hello');

        $stream->changeData('world');

        $this->assertSame('world', (string) $stream);
    }
}
