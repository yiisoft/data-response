<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use function is_array;
use function is_float;
use function is_scalar;
use Psr\Http\Message\ResponseInterface;
use Traversable;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\HasContentTypeTrait;

use Yiisoft\Http\Header;
use Yiisoft\Serializer\XmlSerializer;
use Yiisoft\Strings\NumericHelper;

final class XmlDataResponseFormatter implements DataResponseFormatterInterface
{
    use HasContentTypeTrait;

    /**
     * @var string The Content-Type header for the response.
     */
    private string $contentType = 'application/xml';

    /**
     * @var string The XML version.
     */
    private string $version = '1.0';

    /**
     * @var string The XML encoding.
     */
    private string $encoding = 'UTF-8';

    /**
     * @var string The name of the root element. If set to false, null or is empty then no root tag should be added.
     */
    private string $rootTag = 'response';

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $serializer = new XmlSerializer($this->rootTag, $this->version, $this->encoding);

        if ($dataResponse->hasData()) {
            $content = $serializer->serialize($this->formatData($dataResponse->getData()));
        }

        $response = $dataResponse->getResponse();
        $response->getBody()->write($content ?? '');

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType . '; ' . $this->encoding);
    }

    public function withVersion(string $version): self
    {
        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    public function withEncoding(string $encoding): self
    {
        $new = clone $this;
        $new->encoding = $encoding;
        return $new;
    }

    public function withRootTag(string $rootTag): self
    {
        $new = clone $this;
        $new->rootTag = $rootTag;
        return $new;
    }

    /**
     * Pre-formats the data before serialization.
     *
     * @param mixed $data to format.
     *
     * @return mixed formatted data.
     */
    private function formatData($data)
    {
        if (is_scalar($data)) {
            return $this->formatScalarValue($data);
        }

        if (!is_array($data) && !($data instanceof Traversable)) {
            return $data;
        }

        $formattedData = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $formattedData[$key] = $this->formatScalarValue($value);
            }

            $formattedData[$key] = $this->formatData($value);
        }

        return $formattedData;
    }

    /**
     * Formats scalar value to use in XML node.
     *
     * @param bool|float|int|string $value to format.
     *
     * @return string string representation of the value.
     */
    private function formatScalarValue($value): string
    {
        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (is_float($value)) {
            return NumericHelper::normalize($value);
        }

        return (string) $value;
    }
}
