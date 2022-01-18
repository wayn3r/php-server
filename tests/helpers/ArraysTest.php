<?php

use PHPUnit\Framework\TestCase;
use Helpers\Arrays;

class ArraysTest extends TestCase {

    public function arrayProvider() {
        return [
            [
                [false, false, false],
                false,
            ],
            [
                [false, false, true],
                true,
            ],
            [
                [true, true, true],
                true,
            ],
        ];
    }

    /**
     * @dataProvider arrayProvider
     */
    public function testArraySomeMethod($arrayPassed, $expectedResult) {
        $result = Arrays::some(
            fn ($falsyValue) =>  $falsyValue,
            $arrayPassed
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function arrayStopProvider() {
        return [
            [
                [false, false, false],
                3,
            ],
            [
                [false, false, true],
                3,
            ],
            [
                [true, true, true],
                1,
            ],
        ];
    }

    /**
     * @dataProvider arrayStopProvider
     */
    public function testShouldStopWhenTrueFound($arrayPassed, $expectedCount) {
        $fnCalls = 0;
        Arrays::some(
            function ($falsyValue) use (&$fnCalls) {
                ++$fnCalls;
                return $falsyValue;
            },
            $arrayPassed
        );

        $this->assertEquals($expectedCount, $fnCalls);
    }
}
