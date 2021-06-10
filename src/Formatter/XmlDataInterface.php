<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

/**
 * XmlFormatDataInterface provides methods for formatting objects {@see XmlDataResponseFormatter} into XML data.
 */
interface XmlDataInterface
{
    /**
     * Returns a valid XML tag name {@see https://www.w3.org/TR/REC-xml/#NT-NameStartChar}.
     *
     * @return string The XML tag name.
     */
    public function xmlTagName(): string;

    /**
     * Returns a data to format in XML.
     *
     * The data can be any scalar values, instances of `XmlDataInterface` or `Traversable`,
     * and arrays of any nesting consisting of the above values.
     *
     * @return array The data to format in XML.
     */
    public function xmlData(): array;
}
