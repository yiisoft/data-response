<?php


namespace Yiisoft\DataResponse\Tests\Stub;


use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

class CustomDataResponseFormatter implements DataResponseFormatterInterface
{
    private int $statusCode = 200;
    private array $headers = [];
    private ?string $body = null;
    private string $protocol = '1.1';
    private string $reasonPhrase = '';

    public function withStatusCode(int $statusCode): CustomDataResponseFormatter
    {
        $new = clone $this;
        $new->statusCode = $statusCode;
        return $new;
    }

    public function withHeaders(array $headers): CustomDataResponseFormatter
    {
        $new = clone $this;
        $new->headers = $headers;
        return $new;
    }

    public function withBody(string $body): CustomDataResponseFormatter
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    public function withProtocol(string $protocol): CustomDataResponseFormatter
    {
        $new = clone $this;
        $new->protocol = $protocol;
        return $new;
    }

    public function withReasonPhrase(string $reasonPhrase): CustomDataResponseFormatter
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
