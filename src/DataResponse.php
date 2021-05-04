<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function is_string;

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

    private bool $formatted = false;

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

        if ($this->hasResponseFormatter()) {
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

        throw new RuntimeException('Data must be a string value.');
    }

    /**
     * @param string $name
     *
     * @return array<array-key, string>
     */
    public function getHeader($name): array
    {
        $this->response = $this->formatResponse();
        return $this->response->getHeader($name);
    }

    /**
     * @param string $name
     */
    public function getHeaderLine($name): string
    {
        $this->response = $this->formatResponse();
        return $this->response->getHeaderLine($name);
    }

    /**
     * @return string[][]
     */
    public function getHeaders(): array
    {
        $this->response = $this->formatResponse();
        return $this->response->getHeaders();
    }

    public function getProtocolVersion(): string
    {
        $this->response = $this->formatResponse();
        return $this->response->getProtocolVersion();
    }

    public function getReasonPhrase(): string
    {
        $this->response = $this->formatResponse();
        return $this->response->getReasonPhrase();
    }

    public function getStatusCode(): int
    {
        $this->response = $this->formatResponse();
        return $this->response->getStatusCode();
    }

    /**
     * @param string $name
     */
    public function hasHeader($name): bool
    {
        $this->response = $this->formatResponse();
        return $this->response->hasHeader($name);
    }

    /**
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withAddedHeader($name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withAddedHeader($name, $value);
        $new->formatted = false;
        return $new;
    }

    /**
     * @return static
     */
    public function withBody(StreamInterface $body): self
    {
        $new = clone $this;
        $new->response = $this->response->withBody($body);
        $new->dataStream = $body;
        $new->formatted = false;
        return $new;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     *
     * @return static
     */
    public function withHeader($name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withHeader($name, $value);
        $new->formatted = false;
        return $new;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function withoutHeader($name): self
    {
        $new = clone $this;
        $new->response = $new->formatResponse();
        $new->response = $new->response->withoutHeader($name);
        return $new;
    }

    /**
     * @param string $version
     *
     * @return static
     */
    public function withProtocolVersion($version): self
    {
        $new = clone $this;
        $new->response = $this->response->withProtocolVersion($version);
        $new->formatted = false;
        return $new;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return static
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $new = clone $this;
        $new->response = $this->response->withStatus($code, $reasonPhrase);
        $new->formatted = false;
        return $new;
    }

    /**
     * @return static
     */
    public function withResponseFormatter(DataResponseFormatterInterface $responseFormatter): self
    {
        $new = clone $this;
        $new->responseFormatter = $responseFormatter;
        $new->formatted = false;

        return $new;
    }

    /**
     * @return static
     */
    public function withData($data): self
    {
        $new = clone $this;
        $new->data = $data;
        $new->formatted = false;

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
        return $this->getData() !== null;
    }

    /**
     * @return ResponseInterface
     * @psalm-suppress PossiblyNullReference
     */
    private function formatResponse(): ResponseInterface
    {
        if (!$this->needFormatResponse()) {
            return $this->response;
        }

        $this->clearResponseBody();
        $this->formatted = true;
        $response = $this->responseFormatter->format($this);

        if ($response instanceof self) {
            throw new RuntimeException('DataResponseFormatterInterface should not return instance of DataResponse.');
        }

        return $response;
    }

    private function clearResponseBody(): void
    {
        $this->response->getBody()->rewind();
        $this->response->getBody()->write('');
    }

    private function needFormatResponse(): bool
    {
        return $this->formatted === false && $this->hasResponseFormatter();
    }
}
