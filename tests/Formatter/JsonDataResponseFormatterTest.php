<?php

namespace Yiisoft\Yii\Web\Tests\Data\Formatter;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Yiisoft\DataResponse\Formatter\JsonDataResponseFormatter;
use Yiisoft\DataResponse\DataResponse;

class JsonDataResponseFormatterTest extends TestCase
{
    public function testFormatter(): void
    {
        $factory = new Psr17Factory();
        $dataResponse = new DataResponse(['test' => 'test'], 200, '', $factory);
        $formatter = new JsonDataResponseFormatter();
        $result = $formatter->format($dataResponse);
        $result->getBody()->rewind();

        $this->assertSame('{"test":"test"}', $result->getBody()->getContents());
        $this->assertSame(['application/json'], $result->getHeader('Content-Type'));
    }
}
