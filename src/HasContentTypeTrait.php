<?php

declare(strict_types=1);

namespace Yiisoft\DataResponse;

trait HasContentTypeTrait
{
    public function withContentType(string $contentType): self
    {
        $new = clone $this;
        $new->contentType = $contentType;
        return $new;
    }
}
