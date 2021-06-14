<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

use function get_class;
use function gettype;
use function is_callable;
use function is_object;
use function is_string;
use function sprintf;

/**
 * A wrapper around PSR-7 response that is assigned raw data to be formatted later using a formatter.
 *
 * For example, `['name' => 'Dmitriy']` to be formatted to JSON using
 * {@see \Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter} when {@see DataResponse::getBody()} is called.
 */
final class DataResponse implements ResponseInterface
{
    /**
     * @var mixed
     */
    private $data;
    private bool $formatted = false;
    private ResponseInterface $response;
    private ?StreamInterface $dataStream = null;
    private ?DataResponseFormatterInterface $responseFormatter = null;

    /**
     * @param mixed $data The response data.
     * @param int $code The response status code.
     * @param string $reasonPhrase The response reason phrase associated with the status code.
     * @param ResponseFactoryInterface $responseFactory The response factory instance.
     */
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

        /** @psalm-var mixed $data */
        $data = $this->getData();

        if (is_string($data)) {
            $this->response->getBody()->write($data);
            return $this->dataStream = $this->response->getBody();
        }

        throw new RuntimeException(sprintf(
            'The data is "%s" not a string. To get non-string data, use the "%s::getData()" method.',
            is_object($data) ? get_class($data) : gettype($data),
            self::class,
        ));
    }

    public function getHeader($name): array
    {
        $this->response = $this->formatResponse();
        return $this->response->getHeader($name);
    }

    public function getHeaderLine($name): string
    {
        $this->response = $this->formatResponse();
        return $this->response->getHeaderLine($name);
    }

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

    public function hasHeader($name): bool
    {
        $this->response = $this->formatResponse();
        return $this->response->hasHeader($name);
    }

    /**
     * @inheritDoc
     *
     * @param string $name
     * @param string|string[] $value
     *
     * @return self
     */
    public function withAddedHeader($name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withAddedHeader($name, $value);
        $new->formatted = false;
        return $new;
    }

    /**
     * @inheritDoc
     *
     * @return self
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
     * @inheritDoc
     *
     * @param string $name
     * @param string|string[] $value
     *
     * @return self
     */
    public function withHeader($name, $value): self
    {
        $new = clone $this;
        $new->response = $this->response->withHeader($name, $value);
        $new->formatted = false;
        return $new;
    }

    /**
     * @inheritDoc
     *
     * @param string $name
     *
     * @return self
     */
    public function withoutHeader($name): self
    {
        $new = clone $this;
        $new->response = $new->formatResponse();
        $new->response = $new->response->withoutHeader($name);
        return $new;
    }

    /**
     * @inheritDoc

     * @param string $version
     *
     * @return self
     */
    public function withProtocolVersion($version): self
    {
        $new = clone $this;
        $new->response = $this->response->withProtocolVersion($version);
        $new->formatted = false;
        return $new;
    }

    /**
     * @inheritDoc
     *
     * @param int $code
     * @param string $reasonPhrase
     *
     * @return self
     */
    public function withStatus($code, $reasonPhrase = ''): self
    {
        $new = clone $this;
        $new->response = $this->response->withStatus($code, $reasonPhrase);
        $new->formatted = false;
        return $new;
    }

    /**
     * Returns a new instance with the specified response formatter.
     *
     * @param DataResponseFormatterInterface $responseFormatter
     *
     * @return self
     */
    public function withResponseFormatter(DataResponseFormatterInterface $responseFormatter): self
    {
        $new = clone $this;
        $new->responseFormatter = $responseFormatter;
        $new->formatted = false;
        return $new;
    }

    /**
     * Returns the original instance of the response.
     *
     * @return ResponseInterface The original instance of the response.
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Checks whether the response formatter has been set {@see withResponseFormatter()}.
     *
     * @return bool Whether the formatter has been set.
     */
    public function hasResponseFormatter(): bool
    {
        return $this->responseFormatter !== null;
    }

    /**
     * Returns a new instance with the specified response data.
     *
     * @param mixed $data The response data.
     *
     * @return self
     */
    public function withData($data): self
    {
        $new = clone $this;
        $new->data = $data;
        $new->formatted = false;
        return $new;
    }

    /**
     * Returns the response data.
     *
     * If the response data is a PHP callable, the result of the PHP callable execute will be returned.
     * If the response data or the result of the execution of the PHP callable is an object,
     * a cloned copy of this object will be returned.
     *
     * @return mixed
     */
    public function getData()
    {
        if (is_callable($this->data)) {
            $this->data = ($this->data)();
        }

        return is_object($this->data) ? clone $this->data : $this->data;
    }

    /**
     * Checks whether the response data has been set.
     *
     * @return bool Whether the response data has been set.
     */
    public function hasData(): bool
    {
        return $this->getData() !== null;
    }

    /**
     * Formats the response, if necessary.
     *
     * @return ResponseInterface Formatted response.
     *
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
            throw new RuntimeException(sprintf(
                'The "%s::format()" method should not return instance of "%s".',
                DataResponseFormatterInterface::class,
                self::class,
            ));
        }

        return $response;
    }

    /**
     * Clears a response body.
     */
    private function clearResponseBody(): void
    {
        $this->response->getBody()->rewind();
        $this->response->getBody()->write('');
    }

    /**
     * Checks whether the response needs to be formatted.
     *
     * @return bool Whether the response needs to be formatted.
     */
    private function needFormatResponse(): bool
    {
        return $this->formatted === false && $this->hasResponseFormatter();
    }
}
