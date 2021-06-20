<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Tests\Stub;

use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final class CustomDataResponseFormatter implements DataResponseFormatterInterface
{
    private int $statusCode = 200;
    private array $headers = [];
    private ?string $body = null;
    private string $protocol = '1.1';
    private string $reasonPhrase = '';

    public function withStatusCode(int $statusCode): self
    {
        $new = clone $this;
        $new->statusCode = $statusCode;
        return $new;
    }

    public function withHeaders(array $headers): self
    {
        $new = clone $this;
        $new->headers = $headers;
        return $new;
    }

    public function withBody(string $body): self
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function withProtocol(string $protocol): self
    {
        $new = clone $this;
        $new->protocol = $protocol;
        return $new;
    }

    public function withReasonPhrase(string $reasonPhrase): self
    {
        $new = clone $this;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        return new Response(
            $this->statusCode,
            $this->headers,
            $this->body,
            $this->protocol,
            $this->reasonPhrase
        );
    }
}
