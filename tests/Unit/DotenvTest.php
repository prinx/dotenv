<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;

class DotenvTest extends TestCase
{
    public function testEnvLoad()
    {
        $env = Dotenv::load(realpath(__DIR__.'/../../.env.example'));

        $this->assertInstanceOf(Dotenv::class, $env);
    }

    public function testGetPath()
    {
        $path = realpath(__DIR__.'/../../.env.example');
        loadenv($path);

        $this->assertSame($path, dotenv()->getPath());
    }
}
