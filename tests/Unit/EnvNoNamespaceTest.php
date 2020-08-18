<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;

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
        $this->assertEquals(true, env('EXAMPLE'), 'Retrieve env variable EXAMPLE with env()');
        $this->assertEquals(true, dotenv()->get('EXAMPLE'), 'Retrieve env variable EXAMPLE with dotenv()->get()');
    }

    public function testRetrieveAllEnvVariables()
    {
        $this->assertTrue(env() == ['EXAMPLE' => true], 'Retrieving all env variables using env()');
        $this->assertTrue(allEnv() == ['EXAMPLE' => true], 'Retrieving all env variables using allEnv()');
        $this->assertTrue(dotenv()->all() == ['EXAMPLE' => true], 'Retrieving all env variables using dotenv()->all()');
    }

    public function testAddEnvVariable()
    {
        addEnv('EXAMPLE_2', 'Yes');
        $this->assertEquals('Yes', env('EXAMPLE_2'), 'add env variable EXAMPLE_2');

        dotenv()->add('EXAMPLE_3', 'No');
        $this->assertEquals('No', env('EXAMPLE_3'), 'add env variable EXAMPLE_3 using dotenv()->add()');
    }

    public function testPersistEnvVariable()
    {
        $content = file_get_contents($this->envFile);

        persistEnv('PERSISTENCE', 'all_good');

        loadEnv($this->envFile);
        $this->assertEquals('all_good', env('PERSISTENCE'), 'persist variable (writing directly to the .env file)');

        file_put_contents($this->envFile, $content);
    }

    public function testPersistEnvVariableWithDotenvClassInstance()
    {
        $content = file_get_contents($this->envFile);

        dotenv()->persist('PERSISTENCE', 'all_good');

        loadEnv($this->envFile);

        $this->assertEquals('all_good', env('PERSISTENCE'), 'Writing directly to the .env file using dotenv()->persist()');

        file_put_contents($this->envFile, $content);
    }
}
