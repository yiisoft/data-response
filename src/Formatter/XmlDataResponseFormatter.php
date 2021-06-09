<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;
use Psr\Http\Message\ResponseInterface;
use Traversable;
use Yiisoft\DataResponse\HasContentTypeTrait;
use Yiisoft\Http\Header;
use Yiisoft\Strings\NumericHelper;
use Yiisoft\Strings\StringHelper;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

use function get_class;
use function is_array;
use function is_float;
use function is_int;
use function is_object;
use function iterator_to_array;
use function strpos;

final class XmlDataResponseFormatter implements DataResponseFormatterInterface
{
    use HasContentTypeTrait;

    private const DEFAULT_ITEM_TAG_NAME = 'item';
    private const KEY_ATTRIBUTE_NAME = 'key';

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
     * @var string The name of the root element. If an empty value is set, the root tag should not be added.
     */
    private string $rootTag = 'response';

    /**
     * @var bool If true, the object tags will be formed from the class names,
     * otherwise the {@see DEFAULT_ITEM_TAG_NAME} value will be used.
     */
    private bool $useObjectTags = true;

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

            $content = $dom->saveXML();
        }

        $response = $dataResponse->getResponse();
        $response->getBody()->write($content ?? '');

        return $response->withHeader(Header::CONTENT_TYPE, $this->contentType . '; ' . $this->encoding);
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
     * Returns a new instance with the specified encoding.
     *
     * @param string $encoding The XML encoding. Default is "UTF-8".
     *
     * @return self
     */
    public function withEncoding(string $encoding): self
    {
        $new = clone $this;
        $new->encoding = $encoding;
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
     * Returns a new instance with the specified value, whether to use class names as tags or not.
     *
     * @param bool $useObjectTags If true, the object tags will be formed from the class names,
     * otherwise the {@see DEFAULT_ITEM_TAG_NAME} value will be used. Default is true.
     *
     * @return self
     */
    public function withUseObjectTags(bool $useObjectTags): self
    {
        $new = clone $this;
        $new->useObjectTags = $useObjectTags;
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

        if (is_array($data) || $data instanceof Traversable) {
            $data = $data instanceof Traversable ? iterator_to_array($data) : $data;
            $dataSize = count($data);

            foreach ($data as $name => $value) {
                if (is_int($name) && is_object($value) && !($value instanceof Traversable)) {
                    $this->buildObject($dom, $element, $value, $dataSize > 1 ? $name : null);
                    continue;
                }

                $child = $this->safeCreateDomElement($dom, $name);

                if ($dataSize > 1 && is_int($name)) {
                    $child->setAttribute(self::KEY_ATTRIBUTE_NAME, (string) $name);
                }

                $element->appendChild($child);

                if (is_array($value) || is_object($value)) {
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
        if ($this->useObjectTags) {
            $class = get_class($object);
            $class = strpos($class, 'class@anonymous') === false ? StringHelper::baseName($class) : 'AnonymousClass';
        }

        $child = $this->safeCreateDomElement($dom, $class ?? self::DEFAULT_ITEM_TAG_NAME);

        if ($key !== null) {
            $child->setAttribute(self::KEY_ATTRIBUTE_NAME, (string) $key);
        }

        $element->appendChild($child);
        $array = [];

        foreach ($object as $property => $value) {
            $array[$property] = $value;
        }

        $this->buildXml($dom, $child, $array);
    }

    /**
     * Safely creates a DOMElement instance by the specified tag name, if the tag name is not empty,
     * not integer, and valid. Otherwise the {@see DEFAULT_ITEM_TAG_NAME} value will be used.
     *
     * @see http://stackoverflow.com/questions/2519845/how-to-check-if-string-is-a-valid-xml-element-name/2519943#2519943
     *
     * @param DOMDocument $dom The root DOM document.
     * @param int|null|string $tagName The tag name.
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
     * @param bool|float|int|null|string $value
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
     * @param bool|float|int|null|string $value To format.
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
