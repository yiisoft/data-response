<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * A wrapper around PSR-7 response that is assigned raw data to be formatted later using a formatter.
 *
 * For example, `['name' => 'Dmitriy']` to be formatted to JSON using {@see \Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter}
 * when {@see DataResponse::getBody()} is called.
 */
final class DataResponse implements ResponseInterface
{
    private ResponseInterface $response;

    private $data;

    private ?StreamInterface $dataStream = null;

    private ?DataResponseFormatterInterface $responseFormatter = null;

    public function __construct($data, int $code, string $reasonPhrase, ResponseFactoryInterface $responseFactory)
    {
        $this->response = $responseFactory->createResponse($code, $reasonPhrase);
        $this->data = $data;
    }

    public function getBody(): StreamInterface
    {
        if ($this->dataStream !== null) {
            return $this->dataStream;
        }

        if($this->hasResponseFormatter()) {
            $this->response = $this->formatResponse();
            return $this->dataStream = $this->response->getBody();
        }

        if ($this->data === null) {
            return $this->dataStream = $this->response->getBody();
        }

        $data = $this->getData();
        if (is_string($data)) {
            $this->response->getBody()->write($data);
            return $this->dataStream = $this->response->getBody();
        }

        throw new \RuntimeException('Data must be a string value.');
    }

    public function getHeader($name): array
    {
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function getHeaders(): array
    {
        return $this->response->getHeaders();
    }

    public function getProtocolVersion(): string
    {
        return $this->response->getProtocolVersion();
    }

    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function hasHeader($name): bool
    {
        return $this->response->hasHeader($name);
    }

    public function withAddedHeader($name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withAddedHeader($name, $value);
        return $new;
    }

    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;
        $new->response = $this->response->withBody($body);
        $new->dataStream = $body;
        return $new;
    }

    public function withHeader($name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withHeader($name, $value);
        return $new;
    }

    public function withoutHeader($name): self
    {
        $new = clone $this;
        $new->response = $this->response->withoutHeader($name);
        return $new;
    }

    public function withProtocolVersion($version): self
    {
        $new = clone $this;
        $new->response = $this->response->withProtocolVersion($version);
        return $new;
    }

    public function withStatus($code, $reasonPhrase = ''): self
    {
        $new = clone $this;
        $new->response = $this->response->withStatus($code, $reasonPhrase);
        return $new;
    }

    public function withResponseFormatter(DataResponseFormatterInterface $responseFormatter): self
    {
        $new = clone $this;
        $new->responseFormatter = $responseFormatter;
        return $new;
    }

    public function withData($data): self
    {
        $new = clone $this;
        $new->data = $data;

        return $new;
    }

    public function hasResponseFormatter(): bool
    {
        return $this->responseFormatter !== null;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getData()
    {
        if (is_callable($this->data)) {
            $this->data = ($this->data)();
        }
        return is_object($this->data) ? clone $this->data : $this->data;
    }

    public function hasData(): bool
    {
        return $this->data !== null;
    }

    /**
     * @return ResponseInterface
     * @psalm-suppress PossiblyNullReference
     */
    private function formatResponse(): ResponseInterface
    {
        $response = $this->responseFormatter->format($this);
        if ($response instanceof self) {
            throw new \RuntimeException('DataResponseFormatterInterface should not return instance of DataResponse.');
        }

        return $response;
    }
}
