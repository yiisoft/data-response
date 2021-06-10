<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

/**
 * XmlFormatDataInterface provides methods for formatting objects {@see XmlDataResponseFormatter} as XML data.
 */
interface XmlDataInterface
{
    /**
     * Returns a valid XML tag name {@see https://www.w3.org/TR/REC-xml/#NT-NameStartChar}
     * to use when formatting an object as XML.
     *
     * @return string The XML tag name.
     */
    public function xmlTagName(): string;

    /**
     * Returns an array of data to format as XML.
     *
     * The data can be any scalar values, instances of `XmlDataInterface`,
     * and arrays of any nesting consisting of the above values.
     *
     * @return array The data to format as XML.
     */
    public function xmlData(): array;
}
