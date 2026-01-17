<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Traversable;
use Yiisoft\DataResponse\Modern\DataResponse;
use Yiisoft\DataResponse\Modern\XmlDataInterface;
use Yiisoft\Http\Header;
use Yiisoft\Strings\NumericHelper;

use function is_array;
use function is_float;
use function is_int;
use function is_object;

/**
 * Formats DataResponse as XML.
 */
final class XmlDataResponseFormatter implements MiddlewareInterface
{
    private const DEFAULT_ITEM_TAG_NAME = 'item';

    /**
     * @param string $contentType The Content-Type header for the response.
     * @param string $encoding The encoding for the Content-Type header.
     * @param string $version The XML version.
     * @param string $rootTag The name of the root element. If an empty value is set, the root tag should not be added.
     */
    public function __construct(
        private readonly string $contentType = 'application/xml',
        private readonly string $encoding = 'UTF-8',
        private readonly string $version = '1.0',
        private readonly string $rootTag = 'response',
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$response instanceof DataResponse) {
            return $response;
        }

        $data = $response->data;
        $response = $response->getResponse();

        if (empty($data)) {
            return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
        }

        $dom = new DOMDocument($this->version, $this->encoding);

        if (empty($this->rootTag)) {
            $this->buildXml($dom, $dom, $data);
            $response
                ->getBody()
                ->write((string) $dom->saveXML());
            return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
        }

        $root = new DOMElement($this->rootTag);
        $dom->appendChild($root);
        $this->buildXml($dom, $root, $data);
        $response
            ->getBody()
            ->write((string) $dom->saveXML());
        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }

    /**
     * Builds the data to use in XML.
     *
     * @param DOMDocument $dom The root DOM document.
     * @param DOMDocument|DOMElement $element The current DOM element being processed.
     * @param mixed $data Data for building XML.
     */
    private function buildXml(DOMDocument $dom, $element, mixed $data): void
    {
        if (empty($data)) {
            return;
        }

        if (is_array($data) || ($data instanceof Traversable && !($data instanceof XmlDataInterface))) {
            /** @var int|string $name */
            foreach ($data as $name => $value) {
                if (is_object($value)) {
                    $this->buildObject($dom, $element, $value, $name);
                    continue;
                }

                $child = $this->safeCreateDomElement($dom, $name);
                $element->appendChild($child);

                if (is_array($value)) {
                    $this->buildXml($dom, $child, $value);
                    continue;
                }

                /** @psalm-var scalar $value */

                $this->setScalarValueToDomElement($child, $value);
            }

            return;
        }

        if (is_object($data)) {
            $this->buildObject($dom, $element, $data);
            return;
        }

        /** @psalm-var scalar $data */

        $this->setScalarValueToDomElement($element, $data);
    }

    /**
     * Builds the object to use in XML.
     *
     * @param DOMDocument $dom The root DOM document.
     * @param DOMDocument|DOMElement $element The current DOM element being processed.
     * @param object $object To build.
     * @param int|string|null $tagName The tag name.
     */
    private function buildObject(DOMDocument $dom, $element, object $object, $tagName = null): void
    {
        if ($object instanceof XmlDataInterface) {
            $child = $this->safeCreateDomElement($dom, $object->xmlTagName());

            foreach ($object->xmlTagAttributes() as $name => $value) {
                $child->setAttribute($name, $value);
            }

            $element->appendChild($child);
            $this->buildXml($dom, $child, $object->xmlData());
            return;
        }

        $child = $this->safeCreateDomElement($dom, $tagName);
        $element->appendChild($child);

        if ($object instanceof Traversable) {
            $this->buildXml($dom, $child, $object);
            return;
        }

        $data = [];

        /**
         * @var string $property
         */
        foreach ($object as $property => $value) {
            $data[$property] = $value;
        }

        $this->buildXml($dom, $child, $data);
    }

    /**
     * Safely creates a DOMElement instance by the specified tag name if the tag name is not empty,
     * is not integer, and is valid. Otherwise {@see DEFAULT_ITEM_TAG_NAME} value is used.
     *
     * @see https://stackoverflow.com/questions/2519845/how-to-check-if-string-is-a-valid-xml-element-name/2519943#2519943
     *
     * @param DOMDocument $dom The root DOM document.
     * @param int|string|null $tagName The tag name.
     *
     * @return DOMElement
     */
    private function safeCreateDomElement(DOMDocument $dom, $tagName): DOMElement
    {
        if (empty($tagName) || is_int($tagName)) {
            return $dom->createElement(self::DEFAULT_ITEM_TAG_NAME);
        }

        try {
            if (!$element = $dom->createElement($tagName)) {
                throw new DOMException();
            }
            return $element;
        } catch (DOMException) {
            return $dom->createElement(self::DEFAULT_ITEM_TAG_NAME);
        }
    }

    /**
     * Sets the scalar value to DOM Element instance if the value is not empty.
     *
     * @param DOMDocument|DOMElement $element The current DOM element being processed.
     * @param bool|float|int|string|null $value
     */
    private function setScalarValueToDomElement($element, $value): void
    {
        $value = $this->formatScalarValue($value);

        if ($value !== '') {
            $element->appendChild(new DOMText($value));
        }
    }

    /**
     * Formats scalar value for use in XML node.
     *
     * @param bool|float|int|string|null $value To format.
     *
     * @return string The string representation of the value.
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
