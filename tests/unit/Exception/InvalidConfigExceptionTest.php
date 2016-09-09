<?php

namespace tests\unit\TomPHP\ConfigServiceProvider\Exception;

use LogicException;
use PHPUnit_Framework_TestCase;
use TomPHP\ConfigServiceProvider\Exception\Exception;
use TomPHP\ConfigServiceProvider\Exception\InvalidConfigException;

final class InvalidConfigExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testItImplementsTheBaseExceptionType()
    {
        $this->assertInstanceOf(Exception::class, new InvalidConfigException());
    }

    public function testItIsALogicException()
    {
        $this->assertInstanceOf(LogicException::class, new InvalidConfigException());
    }

    public function testItCanBeCreatedFromTheFileName()
    {
        $this->assertSame(
            '"example.cfg" does not return a PHP array.',
            InvalidConfigException::fromPHPFileError('example.cfg')->getMessage()
        );
    }

    public function testItCanBeCreatedWithAJSONFileError()
    {
        $this->assertSame(
            'Invalid JSON in "example.json": JSON Error Message',
            InvalidConfigException::fromJSONFileError('example.json', 'JSON Error Message')->getMessage()
        );
    }
}