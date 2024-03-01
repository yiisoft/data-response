<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Stub;

use HttpSoft\Message\ResponseTrait;
use HttpSoft\Message\Stream;
use HttpSoft\Message\StreamTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class ResponseFactoryWithCustomStream implements ResponseFactoryInterface
{
    private function __construct(private StreamInterface $stream)
    {
    }

    public static function create(string $stream = 'php://memory', string $mode = 'wb+'): self
    {
        return new self(new Stream($stream, $mode));
    }

    public static function createWithDisabledDetachMethod(): self
    {
        return new self(new class () implements StreamInterface {
            use StreamTrait {
                detach as private detachInternal;
            }

            public function __construct()
            {
                $this->init('php://memory', 'wb+');
            }

            public function detach()
            {
                $this->detachInternal();
                return null;
            }
        });
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new class ($this->stream, $code, $reasonPhrase) implements ResponseInterface {
            use ResponseTrait;

            public function __construct(
                StreamInterface $stream,
                int $statusCode = 200,
                string $reasonPhrase = ''
            ) {
                $this->init($statusCode, $reasonPhrase, [], $stream);
            }
        };
    }
}
