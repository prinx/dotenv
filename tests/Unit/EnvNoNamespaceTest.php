<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;

class EnvNoNamespaceTest extends TestCase
{
    protected $envFile = '';

    public function setUp(): void
    {
        $this->envFile = realpath(__DIR__.'/../../.env.example');
        file_put_contents($this->envFile, 'EXAMPLE=aaa');
        loadenv($this->envFile);
    }

    public function tearDown(): void
    {
        file_put_contents($this->envFile, '');
    }

    public function testloadenv()
    {
        $this->assertInstanceOf(Dotenv::class, dotenv(), 'Test Env load');
    }

    public function testRetrieveEnvVariable()
    {
        $this->assertEquals('aaa', env('EXAMPLE'), 'Retrieve env variable EXAMPLE with env()');
        $this->assertEquals('aaa', dotenv()->get('EXAMPLE'), 'Retrieve env variable EXAMPLE with dotenv()->get()');
    }

    public function testMustReturnBooleanTrueIfBooleanTrueIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=true');
        loadenv($this->envFile);

        $this->assertEquals(true, env('EXAMPLE'), 'Boolean true must be got as boolean true with env()');
        $this->assertEquals(true, dotenv()->get('EXAMPLE'), 'Boolean true must be got as boolean true with dotenv()->get()');
    }

    public function testMustReturnBooleanTrueIfStringTrueIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="true"');
        loadenv($this->envFile);

        $this->assertEquals(true, env('EXAMPLE'), 'String "true" must be got as boolean true with env()');
        $this->assertEquals(true, dotenv()->get('EXAMPLE'), 'String "true" must be got as boolean true with dotenv()->get()');
    }

    public function testMustReturnBooleanFalseIfBooleanFalseIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=false');
        loadenv($this->envFile);

        $this->assertEquals(false, env('EXAMPLE'), 'Boolean false must be got as boolean false with env()');
        $this->assertEquals(false, dotenv()->get('EXAMPLE'), 'Boolean false must be got as boolean false with dotenv()->get()');
    }

    public function testMustReturnBooleanFalseIfStringFalseIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="false"');
        loadenv($this->envFile);

        $this->assertEquals(false, env('EXAMPLE'), 'String "false" must be got as boolean false with env()');
        $this->assertEquals(false, dotenv()->get('EXAMPLE'), 'String "false" must be got as boolean false with dotenv()->get()');
    }

    public function testMustReturnIntegerIfIntegerIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=123');
        loadenv($this->envFile);

        $this->assertEquals(123, env('EXAMPLE'));
        $this->assertEquals(123, dotenv()->get('EXAMPLE'));
    }

    public function testMustReturnStringIfFloatIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=12.3');
        loadenv($this->envFile);

        $this->assertEquals('12.3', env('EXAMPLE'));
        $this->assertEquals('12.3', dotenv()->get('EXAMPLE'));
    }

    public function testMustReturnStringIfStringIntegerIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="123"');
        loadenv($this->envFile);

        $this->assertEquals('123', env('EXAMPLE'));
        $this->assertEquals('123', dotenv()->get('EXAMPLE'));
    }

    public function testRetrieveAllEnvVariables()
    {
        $allEnv = array_merge($_ENV, getenv(), ['EXAMPLE' => 'aaa']);

        $this->assertEquals($allEnv, env(), 'Retrieving all env variables using env()');
        $this->assertEquals($allEnv, allEnv(), 'Retrieving all env variables using allEnv()');
        $this->assertEquals($allEnv, dotenv()->all(), 'Retrieving all env variables using dotenv()->all()');
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

        loadenv($this->envFile);

        persistEnv('PERSISTENCE', 'all_good');

        $this->assertEquals('all_good', env('PERSISTENCE'), 'persist variable (writing directly to the .env file)');

        file_put_contents($this->envFile, $content);
    }

    public function testPersistEnvVariableWithDotenvClassInstance()
    {
        dotenv()->persist('PERSISTENCE', 'all_good');

        loadenv($this->envFile);

        $this->assertEquals('all_good', env('PERSISTENCE'), 'Writing directly to the .env file using dotenv()->persist()');
    }
}
