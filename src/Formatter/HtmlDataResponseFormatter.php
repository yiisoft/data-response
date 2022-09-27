<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\ResponseContentTrait;

use function is_object;
use function is_scalar;
use function method_exists;
use function sprintf;

/**
 * HtmlDataResponseFormatter formats the response data as HTML.
 */
final class HtmlDataResponseFormatter implements DataResponseFormatterInterface
{
    use ResponseContentTrait;

    /**
     * @var string The Content-Type header for the response.
     */
    private string $contentType = 'text/html';

    /**
     * @var string The encoding for the Content-Type header.
     */
    private string $encoding = 'UTF-8';

    /**
     * @inheritDoc
     */
    public function format(DataResponse $dataResponse): ResponseInterface
    {
        /** @var mixed */
        $data = $dataResponse->getData();

        if (!is_scalar($data) && $data !== null && !$this->isStringableObject($data)) {
            throw new RuntimeException(sprintf(
                'Data must be either a scalar value, null, or a stringable object. %s given.',
                get_debug_type($data),
            ));
        }

        return $this->addToResponse($dataResponse->getResponse(), empty($data) ? null : (string) $data);
    }

    /**
     * Checks whether the value is a stringable object.
     *
     * @param mixed $value The value to check.
     *
     * @return bool Whether the value is a stringable object.
     */
    private function isStringableObject(mixed $value): bool
    {
        return is_object($value) && method_exists($value, '__toString');
    }
}
