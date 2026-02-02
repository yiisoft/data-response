<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\Http\HeaderValueHelper;
use Yiisoft\Http\Status;

use function gettype;
use function is_string;
use function sprintf;

final class ContentNegotiatorResponseFactory
{
    /**
     * @param array<string, DataResponseFactoryInterface> $factories
     * @param DataResponseFactoryInterface $fallbackFactory
     */
    public function __construct(
        private readonly array $factories,
        private readonly DataResponseFactoryInterface $fallbackFactory,
    ) {
        $this->checkFormatters($factories);
    }

    public function createResponse(
        RequestInterface $request,
        mixed $data = null,
        int $code = Status::OK,
        string $reasonPhrase = '',
    ): ResponseInterface {
        $accepted = HeaderValueHelper::getSortedAcceptTypes(
            $request->getHeader('Accept'),
        );

        foreach ($accepted as $accept) {
            foreach ($this->factories as $contentType => $factory) {
                if (str_contains($accept, $contentType)) {
                    return $factory->createResponse($data, $code, $reasonPhrase);
                }
            }
        }

        return $this->fallbackFactory->createResponse($data, $code, $reasonPhrase);
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

            if (!($formatter instanceof DataResponseFactoryInterface)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid formatter. A "%s" instance is expected, "%s" is received.',
                        DataResponseFactoryInterface::class,
                        get_debug_type($formatter),
                    ),
                );
            }
        }
    }
}
