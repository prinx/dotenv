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
    }

    public function tearDown(): void
    {
        file_put_contents($this->envFile, '');
    }

    public function testLoadEnvWithDotenvFunction()
    {
        $this->assertInstanceOf(Dotenv::class, dotenv(), 'Test Env load');
    }

    public function testRetrieveEnvVariable()
    {
        file_put_contents($this->envFile, 'EXAMPLE=aaa');
        loadenv($this->envFile);
        $this->directEnvInstance = Dotenv::load($this->envFile);

        $this->assertSame('aaa', env('EXAMPLE'), 'Retrieve env variable EXAMPLE with env()');
        $this->assertSame('aaa', dotenv()->get('EXAMPLE'), 'Retrieve env variable EXAMPLE with dotenv()->get()');
        $this->assertSame('aaa', $this->directEnvInstance->get('EXAMPLE'), 'Retrieve env variable EXAMPLE with Dotenv::load($path)->get()');
    }

    public function testMustReturnBooleanTrueIfBooleanTrueIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=true');
        loadenv($this->envFile);

        $this->assertTrue(env('EXAMPLE'), 'Boolean true must be got as boolean true with env()');
        $this->assertTrue(dotenv()->get('EXAMPLE'), 'Boolean true must be got as boolean true with dotenv()->get()');
    }

    public function testMustReturnBooleanTrueIfStringTrueIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="true"');
        loadenv($this->envFile);

        $this->assertTrue(env('EXAMPLE'), 'String "true" must be got as boolean true with env()');
        $this->assertTrue(dotenv()->get('EXAMPLE'), 'String "true" must be got as boolean true with dotenv()->get()');
    }

    public function testMustReturnBooleanFalseIfBooleanFalseIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=false');
        loadenv($this->envFile);

        $this->assertFalse(env('EXAMPLE'), 'Boolean false must be got as boolean false with env()');
        $this->assertFalse(dotenv()->get('EXAMPLE'), 'Boolean false must be got as boolean false with dotenv()->get()');
    }

    public function testMustReturnBooleanFalseIfStringFalseIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="false"');
        loadenv($this->envFile);

        $this->assertFalse(env('EXAMPLE'), 'String "false" must be got as boolean false with env()');
        $this->assertFalse(dotenv()->get('EXAMPLE'), 'String "false" must be got as boolean false with dotenv()->get()');
    }

    public function testMustReturnStringIntegerIfIntegerIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=123');
        loadenv($this->envFile);

        $this->assertIsString(env('EXAMPLE'));
        $this->assertIsString(dotenv()->get('EXAMPLE'));

        $this->assertEquals(123, env('EXAMPLE'));
        $this->assertEquals(123, dotenv()->get('EXAMPLE'));

        $this->assertNotSame(123, env('EXAMPLE'));
        $this->assertNotSame(123, dotenv()->get('EXAMPLE'));
    }

    public function testMustReturnStringIfFloatIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=12.3');
        loadenv($this->envFile);

        $this->assertSame('12.3', env('EXAMPLE'));
        $this->assertSame('12.3', dotenv()->get('EXAMPLE'));
    }

    public function testMustReturnStringIfStringIntegerIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="123"');
        loadenv($this->envFile);

        $this->assertSame('123', env('EXAMPLE'));
        $this->assertSame('123', dotenv()->get('EXAMPLE'));
    }

    public function testMustReturnNullIfNullIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE=null');
        loadenv($this->envFile);

        $this->assertNull(env('EXAMPLE'));
        $this->assertNull(dotenv()->get('EXAMPLE'));
    }

    public function testMustReturnNullIfStringNullIsValue()
    {
        file_put_contents($this->envFile, 'EXAMPLE="null"');
        loadenv($this->envFile);

        $this->assertNull(env('EXAMPLE'));
        $this->assertNull(dotenv()->get('EXAMPLE'));
    }

    public function testRetrieveAllEnvVariables()
    {
        file_put_contents($this->envFile, 'EXAMPLE=aaa');
        loadenv($this->envFile);
        $this->directEnvInstance = Dotenv::load($this->envFile);

        $all = array_merge($_ENV, getenv(), ['EXAMPLE' => 'aaa']);

        $this->assertSame($all, env(), 'Retrieving all env variables using env()');
        $this->assertSame($all, allenv(), 'Retrieving all env variables using allenv()');
        $this->assertSame($all, dotenv()->all(), 'Retrieving all env variables using dotenv()->all()');
        $this->assertSame($all, dotenv()->get(), 'Retrieving all env variables using dotenv()->get()');
        $this->assertSame($all, $this->directEnvInstance->get(), 'Retrieving all env variables using $this->directEnvInstance->get()');
        $this->assertSame($all, $this->directEnvInstance->all(), 'Retrieving all env variables using $this->directEnvInstance->all()');
    }

    public function testAddEnvVariable()
    {
        file_put_contents($this->envFile, 'EXAMPLE=aaa');
        loadenv($this->envFile);

        addEnv('EXAMPLE_2', 'Yes');
        $this->assertSame('Yes', env('EXAMPLE_2'), 'add env variable EXAMPLE_2');

        dotenv()->add('EXAMPLE_3', 'No');
        $this->assertSame('No', env('EXAMPLE_3'), 'add env variable EXAMPLE_3 using dotenv()->add()');
    }

    public function testPersistString()
    {
        loadenv($this->envFile);

        persistenv('PERSISTENCE', 'all_good');
        $this->assertSame('all_good', env('PERSISTENCE'));

        persistenv('STRING_INTEGER', '123');
        $this->assertIsString(env('STRING_INTEGER'));
        $this->assertSame('123', env('STRING_INTEGER'));
    }

    public function testPersistBool()
    {
        loadenv($this->envFile);

        persistenv('BOOLEAN_TRUE', true);
        $this->assertTrue(env('BOOLEAN_TRUE'));

        persistenv('STRING_TRUE', 'true');
        $this->assertTrue(env('STRING_TRUE'));

        persistenv('BOOLEAN_FALSE', false);
        $this->assertFalse(env('BOOLEAN_FALSE'));

        persistenv('STRING_FALSE', 'false');
        $this->assertFalse(env('STRING_FALSE'));
    }

    public function testPersistNull()
    {
        loadenv($this->envFile);

        persistenv('EXAMPLE', null);
        $this->assertNull(env('EXAMPLE'));

        persistenv('EXAMPLE', 'null');
        $this->assertNull(env('EXAMPLE'));
    }

    public function testPersistStringWithDotenvInstance()
    {
        loadenv($this->envFile);

        dotenv()->persist('PERSISTENCE', 'all_good');
        $this->assertSame('all_good', env('PERSISTENCE'));

        dotenv()->persist('STRING_INTEGER', '123');
        $this->assertIsString(env('STRING_INTEGER'));
        $this->assertSame('123', env('STRING_INTEGER'));
    }

    public function testPersistBoolWithDotenvInstance()
    {
        loadenv($this->envFile);

        dotenv()->persist('BOOLEAN_TRUE', true);
        $this->assertTrue(env('BOOLEAN_TRUE'));

        dotenv()->persist('STRING_TRUE', 'true');
        $this->assertTrue(env('STRING_TRUE'));

        dotenv()->persist('BOOLEAN_FALSE', false);
        $this->assertFalse(env('BOOLEAN_FALSE'));

        dotenv()->persist('STRING_FALSE', 'false');
        $this->assertFalse(env('STRING_FALSE'));
    }

    public function testPersisMustThrowExceptionIfEnvFileDoesNotExist()
    {
        $this->expectException(\RuntimeException::class);
        dotenv()->setPath('.unexistant');
        dotenv()->persist('EXAMPLE', 'aaa');
    }

    public function testGetDefault()
    {
        $this->assertSame('abcd', env('DDDDDDDDD', 'abcd'));
        $this->assertSame(123, env('DDDDDDDDD', 123));
        $this->assertSame(true, env('DDDDDDDDD', true));
    }

    public function testReturnProperBooleanReference()
    {
        file_put_contents($this->envFile, 'EXAMPLE=true'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertTrue(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE=false'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertFalse(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="true"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertTrue(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="false"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertFalse(env('EXAMPLE2'));
    }

    public function testMustReturnProperIntegerReference()
    {
        file_put_contents($this->envFile, 'EXAMPLE=00123'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertSame('00123', env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="00123"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertSame('00123', env('EXAMPLE2'));
    }

    public function testMustReturnProperStringReference()
    {
        file_put_contents($this->envFile, 'EXAMPLE="aaa"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertSame('aaa', env('EXAMPLE2'));
    }

    public function testMustReturnProperTextReference()
    {
        file_put_contents($this->envFile, 'EXAMPLE="aaa"'.PHP_EOL.'EXAMPLE2="OhNiceOne${EXAMPLE}Exactly"');
        loadenv($this->envFile);
        $this->assertSame('OhNiceOneaaaExactly', env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE=aaa'.PHP_EOL.'EXAMPLE2="Oh Nice One ${EXAMPLE}Exactly "');
        loadenv($this->envFile);
        $this->assertSame('Oh Nice One aaaExactly ', env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="aaa"'.PHP_EOL.'EXAMPLE2=Oh Nice One ${EXAMPLE}Exactly ');
        loadenv($this->envFile);
        $this->assertSame('Oh Nice One aaaExactly', env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="aaa"'.PHP_EOL.'EXAMPLE2=Oh Nice One ${EXAMPLE} Exactly ');
        loadenv($this->envFile);
        $this->assertSame('Oh Nice One aaa Exactly', env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="aaa"'.PHP_EOL.'EXAMPLE2 = Oh Nice One ${EXAMPLE} Exactly ');
        loadenv($this->envFile);
        $this->assertSame('Oh Nice One aaa Exactly', env('EXAMPLE2'));
    }

    public function testMustReturnProperNullReference()
    {
        file_put_contents($this->envFile, 'EXAMPLE=null'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertNull(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="null"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertNull(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="Null"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertNull(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="NULL"'.PHP_EOL.'EXAMPLE2="${EXAMPLE}"');
        loadenv($this->envFile);
        $this->assertNull(env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE="null"'.PHP_EOL.'EXAMPLE2=Oh Nice One ${EXAMPLE} Exactly ');
        loadenv($this->envFile);
        $this->assertSame('Oh Nice One  Exactly', env('EXAMPLE2'));

        file_put_contents($this->envFile, 'EXAMPLE=null'.PHP_EOL.'EXAMPLE2 = Oh Nice One ${EXAMPLE} Exactly ');
        loadenv($this->envFile);
        $this->assertSame('Oh Nice One  Exactly', env('EXAMPLE2'));
    }
}
