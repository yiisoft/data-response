<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Formatter;

use LogicException;
use Yiisoft\DataResponse\Formatter\PlainTextDataResponseFormatter;
use Yiisoft\DataResponse\Tests\TestCase;

final class PlainTextDataResponseFormatterTest extends TestCase
{
    public function testCorrectFormat(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $result = (new PlainTextDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            'test',
            $result->getBody()->getContents(),
        );
        $this->assertSame(['text/plain; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testWithEncoding(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $result = (new PlainTextDataResponseFormatter())
            ->withEncoding('ISO-8859-1')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            'test',
            $result->getBody()->getContents(),
        );
        $this->assertSame(['text/plain; charset=ISO-8859-1'], $result->getHeader('Content-Type'));
    }

    public function testWithContentType(): void
    {
        $dataResponse = $this->createDataResponse('test');
        $result = (new PlainTextDataResponseFormatter())
            ->withContentType('text/html')
            ->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            'test',
            $result->getBody()->getContents(),
        );
        $this->assertSame('text/html; charset=UTF-8', $result->getHeaderLine('Content-Type'));
    }

    public function testWithIncorrectType(): void
    {
        $dataResponse = $this->createDataResponse(['test']);
        $formatter = new PlainTextDataResponseFormatter();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Data must be either a scalar value, null, or a stringable object. array given.');
        $formatter->format($dataResponse);
    }

    public function testDataWithNull(): void
    {
        $dataResponse = $this->createDataResponse(null);
        $result = (new PlainTextDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            '',
            $result->getBody()->getContents(),
        );
        $this->assertSame(['text/plain; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testDataWithStringableObject(): void
    {
        $data = new class {
            public function __toString(): string
            {
                return 'test';
            }
        };

        $dataResponse = $this->createDataResponse($data);
        $result = (new PlainTextDataResponseFormatter())->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame(
            'test',
            $result->getBody()->getContents(),
        );
        $this->assertSame(['text/plain; charset=UTF-8'], $result->getHeader('Content-Type'));
    }

    public function testImmutability(): void
    {
        $formatter = new PlainTextDataResponseFormatter();
        $this->assertNotSame($formatter, $formatter->withContentType('text/html'));
        $this->assertNotSame($formatter, $formatter->withEncoding('utf-8'));
    }
}
