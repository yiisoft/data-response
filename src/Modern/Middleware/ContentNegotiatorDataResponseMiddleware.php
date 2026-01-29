<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\DataResponse\Modern\DataStream\DataStream;
use Yiisoft\DataResponse\Modern\Formatter\FormatterInterface;
use Yiisoft\Http\HeaderValueHelper;

use function gettype;
use function is_string;
use function sprintf;

final class ContentNegotiatorDataResponseMiddleware implements MiddlewareInterface
{
    /**
     * @param array<string, FormatterInterface> $formatters
     * @param FormatterInterface|null $fallbackFormatter
     */
    public function __construct(
        private readonly array $formatters = [],
        private readonly ?FormatterInterface $fallbackFormatter = null,
    ) {
        $this->checkFormatters($formatters);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $accepted = HeaderValueHelper::getSortedAcceptTypes(
            $request->getHeader('Accept'),
        );

        $response = $handler->handle($request);
        $body = $response->getBody();
        if (!$body instanceof DataStream || $body->hasFormatter()) {
            return $response;
        }

        foreach ($accepted as $accept) {
            foreach ($this->formatters as $contentType => $formatter) {
                if (str_contains($accept, $contentType)) {
                    $body->changeFormatter($formatter);
                    return $formatter->formatResponse($response);
                }
            }
        }

        if ($this->fallbackFormatter === null) {
            return $response;
        }

        $body->changeFormatter($this->fallbackFormatter);
        return $this->fallbackFormatter->formatResponse($response);
    }

    private function checkFormatters(array $formatters): void
    {
        foreach ($formatters as $contentType => $formatter) {
            if (!is_string($contentType)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid formatter content type. A string is expected, "%s" is received.',
                        gettype($contentType),
                    ),
                );
            }

            if (!($formatter instanceof FormatterInterface)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid formatter. A "%s" instance is expected, "%s" is received.',
                        FormatterInterface::class,
                        get_debug_type($formatter),
                    ),
                );
            }
        }
    }
}
