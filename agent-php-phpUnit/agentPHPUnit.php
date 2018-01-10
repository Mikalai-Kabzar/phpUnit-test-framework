<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */


use PHPUnit_Framework_TestListener as TestListener;
use ReportPortalBasic\Enum\ItemStatusesEnum as ItemStatusesEnum;
use ReportPortalBasic\Enum\ItemTypesEnum as ItemTypesEnum;
use ReportPortalBasic\Enum\LogLevelsEnum as LogLevelsEnum;
use ReportPortalBasic\Service\ReportPortalHTTPService;
use GuzzleHttp\Psr7\Response as Response;

class agentPHPUnit implements TestListener

{

    private const EXECUTOR_TEST = 'ExecutorTest';
    private const PHPUNIT_TEST_SUITE_NAME = 'PHPUnit_Framework_TestSuite';


    private $UUID;
    private $projectName;
    private $host;
    private $timeZone;
    private $launchName;
    private $launchDescription;
    private $className;
    private $classDescription;
    private $testName;
    private $testDescription;

    private $rootItemID;
    private $classItemID;
    private $testItemID;
    private $stepItemID;

    private $isFirstSuite = false;

    /**
     *
     * @var ReportPortalHTTPService
     */
    protected static $httpService;

    /**
     * agentPHPUnit constructor.
     */
    public function __construct($UUID, $host, $projectName, $timeZone, $launchName, $launchDescription)
    {
        $this->UUID = $UUID;
        $this->host = $host;
        $this->projectName = $projectName;
        $this->timeZone = $timeZone;
        $this->launchName = $launchName;
        $this->launchDescription = $launchDescription;

        $this->configureClient();
        self::$httpService->launchTestRun($this->launchName, $this->launchDescription, ReportPortalHTTPService::DEFAULT_LAUNCH_MODE, []);
    }

    /**
     * agentPHPUnit destructor.
     */
    public function __destruct()
    {
        $status = self::getStatusByBool(true);
        $HTTPResult = self::$httpService->finishTestRun($status);
        self::$httpService->finishAll($HTTPResult);
    }

    /**
     * Configure http client.
     */
    private function configureClient()
    {
        $isHTTPErrorsAllowed = false;
        $baseURI = sprintf(ReportPortalHTTPService::BASE_URI_TEMPLATE, $this->host);
        ReportPortalHTTPService::configureClient($this->UUID, $baseURI, $this->host, $this->timeZone, $this->projectName, $isHTTPErrorsAllowed);
        self::$httpService = new ReportPortalHTTPService();
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
//        if ($this->isFirstSuite == false) {
//            $this->configureClient();
//            self::$httpService->launchTestRun($this->launchName, $this->launchDescription, ReportPortalHTTPService::DEFAULT_LAUNCH_MODE, []);
//            $this->isFirstSuite = true;
//        }

        if (self::isRealSuite($suite)) {
            $suiteName = $suite->getName();
            $response = self::$httpService->createRootItem($suiteName, $suiteName . ' tests', []);
            $this->rootItemID = self::getID($response);
        }

        if (!self::isRealSuite($suite)) {
            $className = $suite->getName();
            $stringWithParams = ' - atata';
            $this->className = $className . $stringWithParams;
            $this->classDescription = $stringWithParams;
            $response = self::$httpService->startChildItem($this->rootItemID, $this->classDescription, $this->className, ItemTypesEnum::SUITE, []);
            $this->classItemID = self::getID($response);
        }


        //if (self::EXECUTOR_TEST != $suiteName) {
//        if ($this->isFirstSuite == false) {
//
//            $this->configureClient();
//            //self::$httpService->launchTestRun($this->launchName, $this->launchDescription, ReportPortalHTTPService::DEFAULT_LAUNCH_MODE, []);
//            $this->isFirstSuite = true;
//        }
        //$response = self::$httpService->createRootItem($suiteName, $suiteName . ' tests', []);
        // $this->rootItemID = self::getID($response);
        //}
    }

    /**
     * A test suite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (!self::isRealSuite($suite)) {
            self::$httpService->finishItem($this->classItemID, ItemStatusesEnum::FAILED, $this->classDescription);
        }
        if (self::isRealSuite($suite)) {
            self::$httpService->finishRootItem();
        }


    }

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {

        $testName = $test->getName();
        $stringWithParams = ' - atata';
        $this->testName = $testName . $stringWithParams;
        $this->testDescription = $stringWithParams;

        $response = self::$httpService->startChildItem($this->classItemID, $this->testDescription, $this->testName, ItemTypesEnum::TEST, []);
        $this->testItemID = self::getID($response);
    }

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {

        var_dump($test);
        self::$httpService->finishItem($this->testItemID, ItemStatusesEnum::PASSED, $this->testDescription.$time);
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


    private static function getStatusByBool(bool $isFailedItem)
    {
        if ($isFailedItem) {
            $stringItemStatus = ItemStatusesEnum::FAILED;
        } else {
            $stringItemStatus = ItemStatusesEnum::PASSED;
        }
        return $stringItemStatus;
    }

    /**
     * Get ID from response
     *
     * @param Response $HTTPResponse
     * @return string
     */
    private static function getID(Response $HTTPResponse)
    {
        return json_decode($HTTPResponse->getBody(), true)['id'];
    }


    /**
     * @param PHPUnit_Framework_TestSuite $suite
     * @return bool
     */
    private static function isRealSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $suiteData = var_export($suite->tests(), true);
        return strpos($suiteData, self::PHPUNIT_TEST_SUITE_NAME) != false;
    }
}