<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse\Middleware\AcceptProvider;

use Psr\Http\Message\ServerRequestInterface;

interface AcceptProviderInterface
{
    /**
     * @param ServerRequestInterface $request The request instance.
     * @return string[] The array of acceptable content types in order of preference.
     */
    public function get(ServerRequestInterface $request): array;
}
