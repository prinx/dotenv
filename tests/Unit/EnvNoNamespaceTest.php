<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;

/**
 * @todo The tests need works
 */
class EnvNoNamespaceTest extends TestCase
{
    protected $envFile = '';

    public function __construct()
    {
        parent::__construct();
        $this->envFile = realpath(__DIR__.'/../../.env.example');
    }

    public function testLoadEnv()
    {
        loadEnv($this->envFile);
        $this->assertTrue(is_a(dotenv(), Dotenv::class), 'Test Env load');
    }

    public function testRetrieveEnvVariable()
    {
        $this->assertEquals(true, env('EXAMPLE'), 'Retrieve env variable EXAMPLE');
    }

    public function testAddEnvVariable()
    {
        addEnv('EXAMPLE_2', 'Yes');
        $this->assertEquals('Yes', env('EXAMPLE_2'), 'add env variable EXAMPLE_2');
    }

    public function testPersisitEnvVariable()
    {
        // The test will modify the env file.
        // We Save the content of the file to bde able to revert it back later
        $content = file_get_contents($this->envFile);

        // Test
        persistEnv('PERSISTENCE', 'all_good');

        // Reload the env to get all the variables from the env file instead
        // of the memory-cached env array
        loadEnv($this->envFile);
        $this->assertEquals('all_good', env('PERSISTENCE'), 'persist env variable PERSISTENCE');

        // Revert the file back to its state
        file_put_contents($this->envFile, $content);
    }
}
