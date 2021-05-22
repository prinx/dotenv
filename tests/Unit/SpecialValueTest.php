<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Prinx\Dotenv\SpecialValue;

class SpecialValueTest extends TestCase
{
    /**
     * @var \Prinx\Dotenv\SpecialValue
     */
    protected $specialValue;

    public function setUp(): void
    {
        $this->specialValue = new SpecialValue();
    }

    public function testConfirm()
    {
        $this->assertTrue($this->specialValue->confirm('true'));
        $this->assertTrue($this->specialValue->confirm(true));

        $this->assertTrue($this->specialValue->confirm('false'));
        $this->assertTrue($this->specialValue->confirm(false));

        $this->assertTrue($this->specialValue->confirm('null'));
        $this->assertTrue($this->specialValue->confirm(null));

        $this->assertFalse($this->specialValue->confirm('aaa'));
        $this->assertFalse($this->specialValue->confirm(123));
    }

    public function testConvertTrue()
    {
        $this->assertTrue($this->specialValue->convert('true'));
        $this->assertTrue($this->specialValue->convert(true));
    }

    public function testConvertFalse()
    {
        $this->assertFalse($this->specialValue->convert('false'));
        $this->assertFalse($this->specialValue->convert(false));
    }

    public function testConvertNull()
    {
        $this->assertNull($this->specialValue->convert('null'));
        $this->assertNull($this->specialValue->convert(null));
    }

    public function testConvertNonSpecial()
    {
        $this->assertSame('aaa', $this->specialValue->convert('aaa'));
        $this->assertSame('123', $this->specialValue->convert('123'));
        $this->assertSame(123, $this->specialValue->convert(123));
    }

    public function testReverseTrue()
    {
        $this->assertSame('true', $this->specialValue->reverse('true'));
        $this->assertSame('true', $this->specialValue->reverse(true));
    }

    public function testReverseFalse()
    {
        $this->assertSame('false', $this->specialValue->reverse('false'));
        $this->assertSame('false', $this->specialValue->reverse(false));
    }

    public function testReverseNull()
    {
        $this->assertSame(null, $this->specialValue->reverse('null'));
        $this->assertSame(null, $this->specialValue->reverse(null));
    }

    public function testReverseNonSpecial()
    {
        $this->assertSame('aaa', $this->specialValue->reverse('aaa'));
        $this->assertSame('123', $this->specialValue->reverse('123'));
        $this->assertSame(123, $this->specialValue->reverse(123));
    }
}
