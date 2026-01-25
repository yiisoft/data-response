<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\DataStream\Formatter;

use JsonException;
use Yiisoft\DataResponse\Modern\DataStream\DataFormatterInterface;
use Yiisoft\Json\Json;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Formats data as JSON string.
 */
final class JsonDataFormatter implements DataFormatterInterface
{
    /**
     * @param int $options The encoding options. For more details please refer to
     * {@link https://www.php.net/manual/en/function.json-encode.php}.
     */
    public function __construct(
        private readonly int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ) {}

    /**
     * @throws JsonException
     */
    public function format(mixed $data): string
    {
        if ($data === null) {
            return '';
        }

        return Json::encode($data, $this->options);
    }
}
