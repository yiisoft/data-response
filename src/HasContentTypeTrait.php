<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

trait HasContentTypeTrait
{
    public function withContentType(string $contentType): self
    {
        $clone = clone $this;
        $clone->contentType = $contentType;
        return $clone;
    }
}
