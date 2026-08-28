<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware\AcceptProvider;

use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Http\Header;

final class HeaderAcceptProvider implements AcceptProviderInterface
{
    public function get(ServerRequestInterface $request): array
    {
        $values = [];
        foreach ($request->getHeader(Header::ACCEPT) as $headerValue) {
            $values[] = array_filter(
                array_map(
                    trim(...),
                    explode(',', $headerValue),
                ),
                static fn(string $value) => $value !== '',
            );
        }
        return array_merge(...$values);
    }
}
