<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\DataResponse\ResponseContentTrait;
use Yiisoft\Strings\NumericHelper;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

use function get_class;
use function is_array;
use function is_float;
use function is_int;
use function is_object;
use function sprintf;

final class XmlDataResponseFormatter implements DataResponseFormatterInterface
{
    use ResponseContentTrait;

    private const DEFAULT_ITEM_TAG_NAME = 'item';
    private const KEY_ATTRIBUTE_NAME = 'key';

    /**
     * @var string The Content-Type header for the response.
     */
    private string $contentType = 'application/xml';

    /**
     * @var string The encoding to the Content-Type header.
     */
    private string $encoding = 'UTF-8';

    /**
     * @var string The XML version.
     */
    private string $version = '1.0';

    /**
     * @var string The name of the root element. If an empty value is set, the root tag should not be added.
     */
    private string $rootTag = 'response';

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        if ($dataResponse->hasData()) {
            $dom = new DOMDocument($this->version, $this->encoding);
            $data = $dataResponse->getData();

            if (!empty($this->rootTag)) {
                $root = new DOMElement($this->rootTag);
                $dom->appendChild($root);
                $this->buildXml($dom, $root, $data);
            } else {
                $this->buildXml($dom, $dom, $data);
            }

            $content = (string) $dom->saveXML();
        }

        return $this->addToResponse($dataResponse->getResponse(), $content ?? null);
    }

    /**
     * Returns a new instance with the specified version.
     *
     * @param string $version The XML version. Default is "1.0".
     *
     * @return self
     */
    public function withVersion(string $version): self
    {
        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    /**
     * Returns a new instance with the specified root tag.
     *
     * @param string $rootTag The name of the root element. Default is "response".
     * If an empty value is set, the root tag should not be added.
     *
     * @return self
     */
    public function withRootTag(string $rootTag): self
    {
        $new = clone $this;
        $new->rootTag = $rootTag;
        return $new;
    }

    /**
     * Builds the data to use in XML.
     *
     * @param DOMDocument $dom The root DOM document.
     * @param DOMDocument|DOMElement $element The current DOM element being processed.
     * @param mixed $data Data for building XML.
     */
    private function buildXml(DOMDocument $dom, $element, $data): void
    {
        if (empty($data)) {
            return;
        }

        if (is_array($data)) {
            $dataSize = count($data);

            foreach ($data as $name => $value) {
                if (is_object($value)) {
                    $this->buildObject($dom, $element, $value, $dataSize > 1 && is_int($name) ? $name : null);
                    continue;
                }

                $child = $this->safeCreateDomElement($dom, $name);

                if ($dataSize > 1 && is_int($name)) {
                    $child->setAttribute(self::KEY_ATTRIBUTE_NAME, (string) $name);
                }

                $element->appendChild($child);

                if (is_array($value)) {
                    $this->buildXml($dom, $child, $value);
                    continue;
                }

                $this->setScalarValueToDomElement($child, $value);
            }

            return;
        }

        if (is_object($data)) {
            $this->buildObject($dom, $element, $data);
            return;
        }

        $this->setScalarValueToDomElement($element, $data);
    }

    /**
     * Builds the object to use in XML.
     *
     * @param DOMDocument $dom The root DOM document.
     * @param DOMDocument|DOMElement $element The current DOM element being processed.
     * @param object $object To build.
     * @param int|null $key Key attribute value.
     */
    private function buildObject(DOMDocument $dom, $element, object $object, int $key = null): void
    {
        if (!($object instanceof XmlDataInterface)) {
            throw new RuntimeException(sprintf(
                'The "%s" object must implement the "%s" interface.',
                get_class($object),
                XmlDataInterface::class,
            ));
        }

        $child = $this->safeCreateDomElement($dom, $object->xmlTagName());

        foreach ($object->xmlTagAttributes() as $name => $value) {
            $child->setAttribute($name, $value);
        }

        if ($key !== null && !$child->hasAttribute(self::KEY_ATTRIBUTE_NAME)) {
            $child->setAttribute(self::KEY_ATTRIBUTE_NAME, (string) $key);
        }

        $element->appendChild($child);
        $this->buildXml($dom, $child, $object->xmlData());
    }

    /**
     * Safely creates a DOMElement instance by the specified tag name, if the tag name is not empty,
     * not integer, and valid. Otherwise the {@see DEFAULT_ITEM_TAG_NAME} value will be used.
     *
     * @see http://stackoverflow.com/questions/2519845/how-to-check-if-string-is-a-valid-xml-element-name/2519943#2519943
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
        } catch (DOMException $e) {
            return $dom->createElement(self::DEFAULT_ITEM_TAG_NAME);
        }
    }

    /**
     * Sets the scalar value to Dom Element instance if the value is not empty.
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
     * Formats scalar value to use in XML node.
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
