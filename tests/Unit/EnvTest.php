<?php

namespace Tests\Unit;

use function Prinx\Dotenv\addEnv;
use function Prinx\Dotenv\dotenv;
use function Prinx\Dotenv\env;
use function Prinx\Dotenv\loadEnv;
use function Prinx\Dotenv\persistEnv;
use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;

/**
 * @todo The tests need works
 */
class EnvTest extends TestCase
{

    public function testLoadEnv()
    {
        loadEnv(realpath(__DIR__ . '/../../.env'));
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
        $envFile = dotenv()->getPath();
        $content = file_get_contents($envFile);

        // Test
        persistEnv('PERSISTENCE', 'all_good');
        //Reload the env to get all the variables from the env file instead of the memory-cached env array
        loadEnv(realpath(__DIR__ . '/../../.env'));
        $this->assertEquals('all_good', env('PERSISTENCE'), 'persist env variable PERSISTENCE');

        // Revert the file back to its state
        file_put_contents($envFile, $content);
    }
}
