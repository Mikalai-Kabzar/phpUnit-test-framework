<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */

class VariablesTest extends PHPUnit_Framework_TestCase
{
//    public function testSum1_1()
//    {
//        $this->assertEquals(2, 1 + 1);
//    }
//
//    public function testSum2_4()
//    {
//        $this->assertEquals(6, 2 + 4);
//    }
//
    public function testNegativeSum1_1()
    {
        $this->assertEquals(6, 1 + 1);
    }

    /**
     * @dataProvider additionProvider
     */
    public function tes1tSumDataProviderTests($first, $second, $expected)
    {
        $this->assertEquals($expected, $first + $second);
    }

    public function additionProvider()
    {
        return [
            [0, 0, 0],
            [0, 1, 1],
            [1, 2, 1],
            [2, 1, 3]
        ];
    }
}