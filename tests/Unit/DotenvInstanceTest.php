<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\DotenvInstance;

class DotenvInstanceTest extends TestCase
{
    public function testDotenvInstanceGet()
    {
        $this->assertSame(DotenvInstance::get(), dotenv());
    }
}
