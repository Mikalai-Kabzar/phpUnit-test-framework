<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */

use PHPUnit_Framework_TestCase as TestCase;
use PHPUnit_Framework_TestListener as TestListener;

class agentPHPUnit implements TestListener
{

    private $UUID;
    private $projectName;
    private $host;
    private $timeZone;
    private $launchName;
    private $launchDescription;

    /**
     * agentPHPUnit constructor.
     */
    public function __construct($UUID, $projectName, $host, $timeZone, $launchName, $launchDescription)
    {
        $this->UUID = $UUID;
        $this->projectName = $projectName;
        $this->host = $host;
        $this->timeZone = $timeZone;
        $this->launchName = $launchName;
        $this->launchDescription = $launchDescription;
    }


    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        // TODO: Implement addError() method.
    }

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        // TODO: Implement addFailure() method.
    }

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        // TODO: Implement addIncompleteTest() method.
    }

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception $e
     * @param  float $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        // TODO: Implement addSkippedTest() method.
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        // TODO: Implement startTestSuite() method.
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        // TODO: Implement endTestSuite() method.
    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        // TODO: Implement startTest() method.
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        // TODO: Implement endTest() method.
//        var_dump('EBD TESTS !!!!!!');
//        var_dump($this->UUID);
//        var_dump('EBD TESTS !!!!!!');
//        var_dump($this->projectName);
//        var_dump('EBD TESTS !!!!!!');
//        var_dump($this->host);
//        var_dump('EBD TESTS !!!!!!');
//        var_dump($this->timeZone);
//        var_dump('EBD TESTS !!!!!!');
//        var_dump($this->launchName);
//        var_dump('EBD TESTS !!!!!!');
//        var_dump($this->launchDescription);




    }
}