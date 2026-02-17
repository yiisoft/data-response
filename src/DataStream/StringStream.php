<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\DataStream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function strlen;

use const SEEK_CUR;
use const SEEK_END;
use const SEEK_SET;

/**
 * A read-only stream implementation for string content.
 *
 * @internal
 */
final class StringStream implements StreamInterface
{
    private int $position = 0;
    private bool $closed = false;
    private ?int $size = null;

    public function __construct(
        private readonly string $content,
    ) {}

    public function __toString(): string
    {
        return $this->content;
    }

    public function close(): void
    {
        $this->closed = true;
    }

    public function detach()
    {
        $this->close();
        return null;
    }

    public function getSize(): int
    {
        return $this->getContentSize();
    }

    public function tell(): int
    {
        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        return $this->position;
    }

    public function eof(): bool
    {
        return $this->closed || $this->position >= $this->getContentSize();
    }

    public function isSeekable(): bool
    {
        return !$this->closed;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        $size = $this->getContentSize();

        $newPosition = match ($whence) {
            SEEK_SET => $offset,
            SEEK_CUR => $this->position + $offset,
            SEEK_END => $size + $offset,
            default => throw new RuntimeException('Invalid whence value.'),
        };

        if ($newPosition < 0 || $newPosition > $size) {
            throw new RuntimeException('Invalid seek position.');
        }

        $this->position = $newPosition;
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write(string $string): int
    {
        throw new RuntimeException('Stream is not writable.');
    }

    public function isReadable(): bool
    {
        return !$this->closed;
    }

    public function read(int $length): string
    {
        if ($this->closed) {
            throw new RuntimeException('Stream is closed.');
        }

        if ($length < 0) {
            throw new RuntimeException('Length must be non-negative.');
        }

        if ($this->position >= $this->getContentSize()) {
            return '';
        }

        $data = substr($this->content, $this->position, $length);
        $this->position += strlen($data);

        return $data;
    }

    public function getContents(): string
    {
        return $this->read(
            $this->getContentSize() - $this->position,
        );
    }

    public function getMetadata(?string $key = null)
    {
        if ($this->closed) {
            return $key === null ? [] : null;
        }

        $metadata = [
            'eof' => $this->eof(),
            'seekable' => $this->isSeekable(),
        ];

        if ($key === null) {
            return $metadata;
        }

        return $metadata[$key] ?? null;
    }

    private function getContentSize(): int
    {
        if ($this->size === null) {
            $this->size = strlen($this->content);
        }

        return $this->size;
    }
}
