<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Formatter;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\ResponseContentTrait;

use function get_class;
use function gettype;
use function is_object;
use function is_scalar;
use function method_exists;
use function sprintf;

/**
 * HtmlDataResponseFormatter formats the response data in HTML.
 */
final class HtmlDataResponseFormatter implements DataResponseFormatterInterface
{
    use ResponseContentTrait;

    /**
     * @var string The Content-Type header for the response.
     */
    private string $contentType = 'text/html';

    /**
     * @var string The encoding to the Content-Type header.
     */
    private string $encoding = 'UTF-8';

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $data = $dataResponse->getData();

        if (!is_scalar($data) && $data !== null && !$this->isStringableObject($data)) {
            throw new RuntimeException(sprintf(
                'Data must be a scalar value or null or a stringable object, %s given.',
                is_object($data) ? get_class($data) : gettype($data),
            ));
        }

        return $this->addToResponse($dataResponse->getResponse(), empty($data) ? null : (string) $data);
    }

    private function isStringableObject($value): bool
    {
        return is_object($value) && method_exists($value, '__toString');
    }
}
