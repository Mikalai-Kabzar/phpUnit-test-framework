<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */
class SimpleUnit1Test extends \PHPUnit\Framework\TestCase
{
    /**
     *
     */
    public function test_very_simple_unit_test_1(){
        $this->assertEquals(1,1);
    }

    /**
     *
     */
    public function test_very_simple_unit_test_2(){
        $this->assertEquals(1,6);
    }

    /**
     * @depends test_very_simple_unit_test_2
     */
    public function test_very_simple_unit_test_3(){
        $this->assertEquals(2,2);
    }

    /**
     *@depends test_very_simple_unit_test_3
     */
    public function test_very_simple_unit_test_4(){
        $this->assertEquals(2,1);
    }
}