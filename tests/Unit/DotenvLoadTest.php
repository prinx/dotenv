<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;

class DotenvLoadTest extends TestCase
{
    public function testEnvLoad()
    {
        $env = Dotenv::load(realpath(__DIR__.'/../../.env.example'));

        $this->assertInstanceOf(Dotenv::class, $env);
    }
}
