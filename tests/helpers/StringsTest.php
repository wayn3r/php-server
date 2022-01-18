<?php

use Helpers\Strings;
use PHPUnit\Framework\TestCase;

class StringsTest extends TestCase {

    public function startsWithProvider() {
        return [
            ['hola', 'Hola Mundo', false, true],
            ['hola', 'Hola Mundo', true, false],
            ['adios', 'Hola Mundo', true, false],
            ['adios', 'Hola Mundo', false, false],
        ];
    }

    /**
     * @dataProvider startsWithProvider
     */
    public function testStartsWithMethod(
        string $search,
        string $subject,
        bool $strict,
        bool $expectedResult
    ) {
        $result = Strings::startsWith($search, $subject, $strict);
        $this->assertEquals($expectedResult, $result);
    }

    public function leftTrimProvider() {
        return [
            ['hola', 'Hola Mundo', 'Hola Mundo'],
            ['hola', 'hola Mundo', ' Mundo'],
            ['adios', 'Hola Mundo', 'Hola Mundo'],
            ['adios', 'Hola, adios', 'Hola, adios'],
        ];
    }

    /**
     * @dataProvider leftTrimProvider
     */
    public function testLeftTrimeMethod(
        string $search,
        string $subject,
        string $expectedResult
    ) {
        $result = Strings::leftTrim($search, $subject);
        $this->assertEquals($expectedResult, $result);
    }
}
