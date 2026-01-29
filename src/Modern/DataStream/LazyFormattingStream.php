<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataStream;

use Psr\Http\Message\StreamInterface;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;
use Yiisoft\DataResponse\Modern\Formatter\PlainTextFormatter;

/**
 * @internal
 */
final class LazyFormattingStream implements StreamInterface
{
    private ?StreamInterface $formatted = null;

    public function __construct(
        private readonly mixed $data,
        private readonly FormatterInterface $formatter = new PlainTextFormatter(),
    ) {}

    public function __toString(): string
    {
        return (string) $this->getFormatted();
    }

    public function close(): void
    {
        $this->getFormatted()->close();
    }

    public function detach()
    {
        return $this->getFormatted()->detach();
    }

    public function getSize(): ?int
    {
        return $this->getFormatted()->getSize();
    }

    public function tell(): int
    {
        return $this->getFormatted()->tell();
    }

    public function eof(): bool
    {
        return $this->getFormatted()->eof();
    }

    public function isSeekable(): bool
    {
        return $this->getFormatted()->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->getFormatted()->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->getFormatted()->rewind();
    }

    public function isWritable(): bool
    {
        return $this->getFormatted()->isWritable();
    }

    public function write(string $string): int
    {
        return $this->getFormatted()->write($string);
    }

    public function isReadable(): bool
    {
        return $this->getFormatted()->isReadable();
    }

    public function read(int $length): string
    {
        return $this->getFormatted()->read($length);
    }

    public function getContents(): string
    {
        return $this->getFormatted()->getContents();
    }

    public function getMetadata(?string $key = null)
    {
        return $this->getFormatted()->getMetadata($key);
    }

    /**
     * Gets or creates the inner stream by formatting the data.
     */
    public function getFormatted(): StreamInterface
    {
        if ($this->formatted !== null) {
            return $this->formatted;
        }

        $content = $this->formatter->formatData($this->data);

        $this->formatted = $content instanceof StreamInterface
            ? $content
            : new StringStream($content);

        return $this->formatted;
    }
}

