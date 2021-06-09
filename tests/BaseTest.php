<?php



use PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected function expectExceptionMessageMatches(string $string): void
    {
        # todo: write some assertion or copy from further php unit version
    }
}
