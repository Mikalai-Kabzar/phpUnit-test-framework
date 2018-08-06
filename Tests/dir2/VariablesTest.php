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
    public function tes1tSum1_1()
    {
        $this->assertEquals(2, 1 + 1);
    }

    /**
     *
     */
    public function tes1tSum2_4()
    {
        $this->assertEquals(6, 2 + 4);
    }

    /**
     *
     */
    public function tes1tNegativeSum1_1()
    {
        $this->assertEquals(2, 1 + 1);
    }

    /**
     * @dataProvider additionProvider
     * @param $first
     * @param $second
     * @param $expected
     */
    public function tes1tSumDataProviderTests1($first, $second, $expected)
    {
        $this->assertEquals($expected, $first + $second);
    }

    /**
     * @dataProvider additionProvider
     * @param $a
     * @param $b
     * @param $expected
     */
    public function tes1tSumDataProviderTests2($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
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