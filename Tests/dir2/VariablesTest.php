<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */

class VariablesTest extends PHPUnit\Framework\TestCase
{
    /**
     *
     */
    public function testSum1_1()
    {
        $this->assertEquals(2, 1 + 1);
        //sleep(3);
    }

    /**
     *
     */
    public function testSum2_4()
    {
        $this->assertEquals(6, 2 + 4);
        //sleep(3);
    }

    /**
     *
     */
    public function testZNegativeSum1_1()
    {
        //sleep(3);
        $this->assertEquals(2, 1 + 1);
    }

    /**
     * @dataProvider additionProvider
     * @param $first
     * @param $second
     * @param $expected
     */
    public function testSumDataProviderTests1($first, $second, $expected)
    {
        $this->assertEquals($expected, $first + $second);
    }

    /**
     * @dataProvider additionProvider
     * @param $a
     * @param $b
     * @param $expected
     */
    public function testSumDataProviderTests2($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
        //sleep(3);
    }

    /**
     * @dataProvider additionProvider
     * @param $a
     * @param $b
     * @param $expected
     */
    public function testAdd($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
    }

    /**
     * @return array
     */
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