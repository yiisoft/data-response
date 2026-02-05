<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\ResponseFactory;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Yiisoft\Http\HeaderValueHelper;
use Yiisoft\Http\Status;

use function gettype;
use function is_string;
use function sprintf;

/**
 * Factory that selects a response factory based on the request's `Accept` header.
 */
final class ContentNegotiatorResponseFactory
{
    /**
     * @param DataResponseFactoryInterface[] $factories Map of content types to factories.
     * For example: `['application/json' => $jsonFactory, 'application/xml' => $xmlFactory]`.
     * @param DataResponseFactoryInterface|RequestHandlerInterface $fallback Factory or request handler
     * to use when no match is found.
     *
     * @psalm-param array<string, DataResponseFactoryInterface> $factories
     */
    public function __construct(
        private readonly array $factories,
        private readonly DataResponseFactoryInterface|RequestHandlerInterface $fallback,
    ) {
        $this->checkFactories($factories);
    }

    /**
     * Creates an HTTP response using a factory selected based on the request's `Accept` header.
     *
     * @param ServerRequestInterface $request The request to extract the `Accept` header from.
     * @param mixed $data The response data to be included in the response body.
     * @param int $code The HTTP status code for the response.
     * @param string $reasonPhrase The reason phrase associated with the status code.
     *
     * @return ResponseInterface The created HTTP response.
     */
    public function createResponse(
        ServerRequestInterface $request,
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

        if ($this->fallback instanceof RequestHandlerInterface) {
            return $this->fallback->handle($request);
        }

        return $this->fallback->createResponse($data, $code, $reasonPhrase);
    }

    private function checkFactories(array $factories): void
    {
        foreach ($factories as $contentType => $factory) {
            if (!is_string($contentType)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid factory content type. A string is expected, "%s" is received.',
                        gettype($contentType),
                    ),
                );
            }

            if (!($factory instanceof DataResponseFactoryInterface)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid factory. A "%s" instance is expected, "%s" is received.',
                        DataResponseFactoryInterface::class,
                        get_debug_type($factory),
                    ),
                );
            }
        }
    }
}
