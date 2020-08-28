<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\HasContentTypeTrait;
use Yiisoft\Http\Header;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

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
        if (!is_scalar($data) && !is_null($data) && !$this->isStringableObject($data)) {
            throw new \RuntimeException('Data must be a scalar value or null or a stringable object.');
        }

        $response = $dataResponse->getResponse();
        $response->getBody()->write((string)$data);

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType . '; charset=' . $this->encoding);
    }

    public function withEncoding(string $encoding): self
    {
        $formatter = clone $this;
        $formatter->encoding = $encoding;
        return $formatter;
    }

    private function isStringableObject($value): bool
    {
        return is_object($value) && method_exists($value, '__toString');
    }
}
