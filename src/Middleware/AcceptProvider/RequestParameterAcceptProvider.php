<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware\AcceptProvider;

use Psr\Http\Message\ServerRequestInterface;

use Yiisoft\Http\Method;

use function is_array;
use function is_string;

final class RequestParameterAcceptProvider implements AcceptProviderInterface
{
    public function __construct(
        public string $name = 'format',
    ) {
    }

    public function get(ServerRequestInterface $request): array
    {
        return $request->getMethod() === Method::GET
            ? $this->fromQueryParams($request)
            : [...$this->fromParsedBody($request), ...$this->fromQueryParams($request)];
    }

    /**
     * @return string[]
     */
    public function fromQueryParams(ServerRequestInterface $request): array
    {
        return $this->prepareValue($request->getQueryParams()[$this->name] ?? null);
    }

    /**
     * @return string[]
     */
    private function fromParsedBody(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();
        return is_array($body)
            ? $this->prepareValue($body[$this->name] ?? null)
            : [];
    }

    /**
     * @return string[]
     */
    private function prepareValue(mixed $value): array
    {
        if (!is_string($value)) {
            return [];
        }

        return array_filter(
            array_map(
                trim(...),
                explode(',', $value),
            ),
            static fn(string $accept) => $accept !== '',
        );
    }
}
