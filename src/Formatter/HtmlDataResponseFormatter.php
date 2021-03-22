<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\HasContentTypeTrait;
use Yiisoft\Http\Header;

final class HtmlDataResponseFormatter implements DataResponseFormatterInterface
{
    use HasContentTypeTrait;

    /**
     * @var string the Content-Type header for the response
     */
    private string $contentType = 'text/html';

    /**
     * @var string the XML encoding.
     */
    private string $encoding = 'UTF-8';

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $data = $dataResponse->getData();
        if (!is_scalar($data) && null !== $data && !$this->isStringableObject($data)) {
            $type = is_object($data) ? get_class($data) : gettype($data);
            throw new RuntimeException(sprintf('Data must be a scalar value or null or a stringable object, %s given.', $type));
        }

        $response = $dataResponse->getResponse();
        $response->getBody()->write((string)$data);

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType . '; charset=' . $this->encoding);
    }

    public function withEncoding(string $encoding): self
    {
        $new = clone $this;
        $new->encoding = $encoding;
        return $new;
    }

    private function isStringableObject($value): bool
    {
        return is_object($value) && method_exists($value, '__toString');
    }
}
