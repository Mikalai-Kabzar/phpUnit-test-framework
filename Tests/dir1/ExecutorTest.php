<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */

class ExecutorTest extends PHPUnit_Framework_TestCase
{
    public function testTrueReturnedByExecutor(){
        $executor = new Executor();
        $this->assertTrue($executor->returnTrue());
    }

    public function testFalseReturnedByExecutor(){
        $executor = new Executor();
        $this->assertFalse($executor->returnTrue());
    }
}