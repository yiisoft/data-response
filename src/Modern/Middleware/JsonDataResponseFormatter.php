<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Modern\Middleware;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\Modern\DataResponse;
use Yiisoft\Http\Header;
use Yiisoft\Json\Json;

use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Formats DataResponse as JSON.
 */
final class JsonDataResponseFormatter implements MiddlewareInterface
{
    /**
     * @param string $contentType The Content-Type header for the response.
     * @param string $encoding The encoding for the Content-Type header.
     * @param int $options The encoding options. For more details please refer to
     * {@link https://www.php.net/manual/en/function.json-encode.php}.
     */
    public function __construct(
        private readonly string $contentType = 'application/json',
        private readonly string $encoding = 'UTF-8',
        private readonly int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    ) {}

    /**
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if (!$response instanceof DataResponse) {
            return $response;
        }

        $data = $response->data;
        $response = $response->getResponse();

        if ($data !== null) {
            $response
                ->getBody()
                ->write(Json::encode($data, $this->options));
        }

        return $response->withHeader(Header::CONTENT_TYPE, "$this->contentType; charset=$this->encoding");
    }
}
