<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\Dotenv;
use function Prinx\Dotenv\addEnv;
use function Prinx\Dotenv\allEnv;
use function Prinx\Dotenv\dotenv;
use function Prinx\Dotenv\env;
use function Prinx\Dotenv\loadenv;
use function Prinx\Dotenv\persistEnv;

class EnvNamespacedTest extends TestCase
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
        $this->assertEquals($allEnv, dotenv()->get(), 'Retrieving all env variables using dotenv()->all()');
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
        persistEnv('PERSISTENCE', 'all_good');
        $this->assertEquals('all_good', env('PERSISTENCE'));

        persistEnv('BOOLEAN_TRUE', true);
        $this->assertTrue(env('BOOLEAN_TRUE'));

        persistEnv('STRING_TRUE', 'true');
        $this->assertTrue(env('STRING_TRUE'));

        persistEnv('BOOLEAN_FALSE', false);
        $this->assertFalse(env('BOOLEAN_FALSE'));

        persistEnv('STRING_FALSE', 'false');
        $this->assertFalse(env('STRING_FALSE'));

        persistEnv('INTEGER', 123);
        $this->assertIsInt(env('INTEGER'));
        $this->assertEquals(123, env('INTEGER'));

        persistEnv('STRING_INTEGER', '123');
        $this->assertIsString(env('STRING_INTEGER'));
        $this->assertEquals('123', env('STRING_INTEGER'));
    }

    public function testPersistEnvVariableWithDotenvClassInstance()
    {
        dotenv()->persist('PERSISTENCE', 'all_good');
        $this->assertEquals('all_good', env('PERSISTENCE'));

        dotenv()->persist('BOOLEAN_TRUE', true);
        $this->assertTrue(env('BOOLEAN_TRUE'));

        dotenv()->persist('STRING_TRUE', 'true');
        $this->assertTrue(env('STRING_TRUE'));

        dotenv()->persist('BOOLEAN_FALSE', false);
        $this->assertFalse(env('BOOLEAN_FALSE'));

        dotenv()->persist('STRING_FALSE', 'false');
        $this->assertFalse(env('STRING_FALSE'));

        dotenv()->persist('INTEGER', 123);
        $this->assertIsInt(env('INTEGER'));
        $this->assertEquals(123, env('INTEGER'));

        dotenv()->persist('STRING_INTEGER', '123');
        $this->assertIsString(env('STRING_INTEGER'));
        $this->assertEquals('123', env('STRING_INTEGER'));
    }

    public function testGetDefault()
    {
        $this->assertEquals('abcd', env('DDDDDDDDD', 'abcd'));
        $this->assertEquals(123, env('DDDDDDDDD', 123));
        $this->assertEquals(true, env('DDDDDDDDD', true));
    }

    public function testReturnProperReference()
    {
        file_put_contents($this->envFile, 'EXAMPLE=123'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadEnv($this->envFile);
        $this->assertEquals(123, env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE=true'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadEnv($this->envFile);
        $this->assertEquals(true, env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE=false'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadEnv($this->envFile);
        $this->assertEquals(false, env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="true"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadEnv($this->envFile);
        $this->assertEquals(true, env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="false"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadEnv($this->envFile);
        $this->assertEquals(false, env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="aaa"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadEnv($this->envFile);
        $this->assertEquals('aaa', env('EXAMPLE2'));
    }
}
