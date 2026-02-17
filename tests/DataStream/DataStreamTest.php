<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\DataStream;

use LogicException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\DataResponse\DataStream\DataStream;
use Yiisoft\DataResponse\Formatter\HtmlFormatter;
use Yiisoft\DataResponse\Formatter\JsonFormatter;
use Yiisoft\DataResponse\Tests\Support\StubFormatter;
use Yiisoft\Test\Support\HttpMessage\StringStream;

use const SEEK_CUR;
use const SEEK_END;

final class DataStreamTest extends TestCase
{
    public function testBase(): void
    {
        $stream = new DataStream('test data');

        $this->assertFalse($stream->hasFormatter());
        $this->assertNull($stream->getFormatter());
        $this->assertSame('test data', $stream->getData());
    }

    public function testGetFormattedWithoutFormatter(): void
    {
        $stream = new DataStream('test');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Formatter is not set.');
        (string) $stream;
    }

    public function testFormatter(): void
    {
        $formatter = new JsonFormatter();
        $stream = new DataStream('test', $formatter);

        $this->assertTrue($stream->hasFormatter());
        $this->assertSame($formatter, $stream->getFormatter());
        $this->assertSame('"test"', (string) $stream);
    }

    public function testChangeFormatter(): void
    {
        $formatter = new JsonFormatter();
        $stream = new DataStream('test', new HtmlFormatter());

        $stream->changeFormatter($formatter);

        $this->assertTrue($stream->hasFormatter());
        $this->assertSame($formatter, $stream->getFormatter());
        $this->assertSame('"test"', (string) $stream);
    }

    public function testCloseStreamOnChangeFormatter(): void
    {
        $formatted = new StringStream();
        $stream = new DataStream('hello', new StubFormatter($formatted));

        $stream->getContents();
        $stream->changeFormatter(new JsonFormatter());

        $this->assertTrue($formatted->isClosed());
        $this->assertFalse($formatted->isDetached());
    }

    public function testChangeData(): void
    {
        $stream = new DataStream('hello', new HtmlFormatter());

        $stream->changeData('world');

        $this->assertSame('world', (string) $stream);
        $this->assertSame('world', $stream->getData());
    }

    public function testCloseStreamOnChangeData(): void
    {
        $formatted = new StringStream();
        $stream = new DataStream('hello', new StubFormatter($formatted));

        $stream->getContents();
        $stream->changeData('world');

        $this->assertTrue($formatted->isClosed());
        $this->assertFalse($formatted->isDetached());
    }

    public function testClose(): void
    {
        $formatted = new StringStream();
        $stream = new DataStream('data', new StubFormatter($formatted));

        $stream->close();

        $this->assertTrue($formatted->isClosed());
    }

    public function testDetach(): void
    {
        $formatted = new StringStream();
        $stream = new DataStream('data', new StubFormatter($formatted));

        $stream->detach();

        $this->assertTrue($formatted->isDetached());
    }

    public function testGetSize(): void
    {
        $formatted = new StringStream('hello');
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->getSize();

        $this->assertSame(5, $result);
    }

    public function testTell(): void
    {
        $formatted = new StringStream('hello', position: 3);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->tell();

        $this->assertSame(3, $result);
    }

    #[TestWith([true, 5])]
    #[TestWith([false, 3])]
    public function testEof(bool $expected, int $position): void
    {
        $formatted = new StringStream('hello', position: $position);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->eof();

        $this->assertSame($expected, $result);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testIsSeekable(bool $expected): void
    {
        $formatted = new StringStream(seekable: $expected);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->isSeekable();

        $this->assertSame($expected, $result);
    }

    public function testSeekSet(): void
    {
        $formatted = new StringStream('hello');
        $stream = new DataStream('data', new StubFormatter($formatted));

        $stream->seek(3);

        $this->assertSame(3, $formatted->getPosition());
    }

    public function testSeekCur(): void
    {
        $formatted = new StringStream('hello', position: 1);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $stream->seek(3, SEEK_CUR);

        $this->assertSame(4, $formatted->getPosition());
    }

    public function testRewind(): void
    {
        $formatted = new StringStream('hello', position: 3);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $stream->rewind();

        $this->assertSame(0, $formatted->getPosition());
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testIsWritable(bool $expected): void
    {
        $formatted = new StringStream(writable: $expected);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->isWritable();

        $this->assertSame($expected, $result);
    }

    public function testWrite(): void
    {
        $formatted = new StringStream('hello', position: 5);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->write(', world');

        $this->assertSame(7, $result);
        $this->assertSame('hello, world', (string) $formatted);
    }

    #[TestWith([true])]
    #[TestWith([false])]
    public function testIsReadable(bool $expected): void
    {
        $formatted = new StringStream(readable: $expected);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->isReadable();

        $this->assertSame($expected, $result);
    }

    public function testRead(): void
    {
        $formatted = new StringStream('abcdef');
        $stream = new DataStream('data', new StubFormatter($formatted));

        $this->assertSame('ab', $stream->read(2));
        $this->assertSame('cde', $stream->read(3));
    }

    public function testGetContents(): void
    {
        $formatted = new StringStream('hello');
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->getContents();

        $this->assertSame('hello', $result);
    }

    public function testGetMetadata(): void
    {
        $formatted = new StringStream('hello', metadata: ['foo' => 'bar']);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $result = $stream->getMetadata();

        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function testGetMetadataWithKey(): void
    {
        $formatted = new StringStream('hello', metadata: ['foo' => 'bar']);
        $stream = new DataStream('data', new StubFormatter($formatted));

        $this->assertSame('bar', $stream->getMetadata('foo'));
        $this->assertNull($stream->getMetadata('not-exists'));
    }

    public function testStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $this->assertSame('test', (string) $stream);
        $this->assertSame('test', $stream->getContents());
        $this->assertSame(4, $stream->getSize());
        $this->assertSame(
            [
                'eof' => true,
                'seekable' => true,
            ],
            $stream->getMetadata(),
        );
        $this->assertTrue($stream->getMetadata('eof'));
        $this->assertTrue($stream->getMetadata('seekable'));
        $this->assertNull($stream->getMetadata('not-exists'));
        $this->assertFalse($stream->isWritable());
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isSeekable());
    }

    public function testTellInClosedStreamWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->tell();
    }

    public function testSeekInClosedStreamWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->seek(0);
    }

    public function testRewindInClosedStreamWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->rewind();
    }

    public function testReadInClosedStreamWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->read(5);
    }

    public function testDetachWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $result = $stream->detach();

        $this->assertNull($result);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is closed.');
        $stream->read(5);
    }

    public function testTellWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $this->assertSame(0, $stream->tell());

        $stream->read(2);
        $this->assertSame(2, $stream->tell());

        $stream->read(100);
        $this->assertSame(4, $stream->tell());
    }

    public function testSeekSetWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $stream->seek(2);
        $result = $stream->read(2);

        $this->assertSame('st', $result);
    }

    public function testSeekCurWithStringData(): void
    {
        $stream = new DataStream('abcdef', new HtmlFormatter());
        $stream->read(1);

        $stream->seek(2, SEEK_CUR);
        $result = $stream->read(2);

        $this->assertSame('de', $result);
    }

    public function testSeekEndWithStringData(): void
    {
        $stream = new DataStream('abcdefg', new HtmlFormatter());

        $stream->seek(-3, SEEK_END);
        $result = $stream->read(3);

        $this->assertSame('efg', $result);
    }

    public function testInvalidWhenceWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid whence value.');
        $stream->seek(1, 9);
    }

    #[TestWith([-5])]
    #[TestWith([100])]
    public function testInvalidOffsetWithStringData(int $value): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid seek position.');
        $stream->seek($value);
    }

    public function testGetMetadataInClosedStreamWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->close();

        $this->assertSame([], $stream->getMetadata());
        $this->assertSame(null, $stream->getMetadata('eof'));
    }

    public function testWriteWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Stream is not writable.');
        $stream->write('hello');
    }

    public function testReadNegativeValueWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Length must be non-negative.');
        $stream->read(-1);
    }

    public function testReadOverValueWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->getContents();

        $result = $stream->read(2);

        $this->assertSame('', $result);
    }

    public function testRewindWithStringData(): void
    {
        $stream = new DataStream('test', new HtmlFormatter());
        $stream->getContents();

        $stream->rewind();
        $result = $stream->read(2);

        $this->assertSame('te', $result);
    }
}
