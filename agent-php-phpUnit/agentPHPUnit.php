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

    private const PHPUNIT_TEST_SUITE_NAME = 'PHPUnit_Framework_TestSuite';
    private const PHPUNIT_TEST_SUITE_DATAPROVIDER_NAME = 'PHPUnit_Framework_TestSuite_DataProvider';


    protected $tests = array();

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
     * @param PHPUnit_Framework_Test $test
     * @return null|string
     */
    private function getTestStatus(PHPUnit_Framework_Test $test)
    {
        $status = $test->getStatus();
        $statusResult = null;
        if ($status == PHPUnit_Runner_BaseTestRunner::STATUS_PASSED) {
            $statusResult = ItemStatusesEnum::PASSED;
        } else if ($status == PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE) {
            $statusResult = ItemStatusesEnum::FAILED;
        } else if ($status == PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED) {
            $statusResult = ItemStatusesEnum::SKIPPED;
        } else if ($status == PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE) {
            $statusResult = ItemStatusesEnum::STOPPED;
        } else if ($status == PHPUnit_Runner_BaseTestRunner::STATUS_ERROR) {
            $statusResult = ItemStatusesEnum::CANCELLED;
        } else {
            $statusResult = ItemStatusesEnum::CANCELLED;
        }
        return $statusResult;
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
        $this->addSetOfLogMessages($test, $e, LogLevelsEnum::FATAL, $this->testItemID);
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
        $this->addSetOfLogMessages($test, $e, LogLevelsEnum::ERROR, $this->testItemID);
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
        $this->addSetOfLogMessages($test, $e, LogLevelsEnum::WARN, $this->testItemID);
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
        $this->addSetOfLogMessages($test, $e, LogLevelsEnum::WARN, $this->testItemID);
    }

    /**
     * A test suite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        //var_dump($suite);


        $suiteData = var_export($suite->tests(), true);
        $json = json_encode($suiteData);
        //@var PHPUnit_Util_TestSuiteIterator
        //$iterator = get_object_vars($suite->{'groups'});
        $iterator = $suite->getGroups();
        var_dump($iterator);






        if (self::isRealSuite($suite)) {
            $suiteName = $suite->getName();
            $response = self::$httpService->createRootItem($suiteName, '', []);
            $this->rootItemID = self::getID($response);
        }
        if (!self::isRealSuite($suite)) {
            $className = $suite->getName();
            $this->className = $className;
            $this->classDescription = '';
            $response = self::$httpService->startChildItem($this->rootItemID, $this->classDescription, $this->className, ItemTypesEnum::SUITE, []);
            $this->classItemID = self::getID($response);
        }
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
        $this->testName = $test->getName();
        $this->testDescription = '';
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
        $testStatus = $this->getTestStatus($test);
        self::$httpService->finishItem($this->testItemID, $testStatus, $time . ' seconds');
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param $logLevelsEnum
     * @param $testItemID
     */
    private function addSetOfLogMessages(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $logLevelsEnum, $testItemID)
    {
        $errorMessage = $e->toString();
        self::$httpService->addLogMessage($testItemID, $errorMessage, $logLevelsEnum);

        $this->AddLogMessages($test, $e, $logLevelsEnum, $testItemID);

        $trace = $e->getTraceAsString();
        self::$httpService->addLogMessage($testItemID, $trace, $logLevelsEnum);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param $logLevelsEnum
     * @param $testItemID
     */
    private function AddLogMessages(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $logLevelsEnum, $testItemID)
    {
        $className = get_class($test);
        $traceArray = $e->getTrace();
        $arraySize = sizeof($traceArray);
        $foundedFirstMatch = false;
        $counter = 0;
        while (!$foundedFirstMatch and $counter < $arraySize) {
            if (strpos($traceArray[$counter]["file"], $className) != false) {
                $fileName = $traceArray[$counter]["file"];
                $fileLine = $traceArray[$counter]["line"];
                $function = $traceArray[$counter]["function"];
                $assertClass = $traceArray[$counter]["class"];
                $type = $traceArray[$counter]["type"];
                $args = implode(',', $traceArray[$counter]["args"]);
                self::$httpService->addLogMessage($testItemID, $assertClass . $type . $function . '(' . $args . ')', $logLevelsEnum);
                self::$httpService->addLogMessage($testItemID, $fileName . ':' . $fileLine, $logLevelsEnum);

                $foundedFirstMatch = true;
            }
            $counter++;
        }
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
        //var_dump(sizeof($suite->tests()).'_');
//        $suiteData = var_export($suite->tests(), true);
//        $json = json_encode($suiteData);
//        //@var PHPUnit_Util_TestSuiteIterator
//        $iterator = $suite;
//        var_dump($iterator);
        //var_dump(get_object_vars($suite));
//        foreach ($suite as $record):
//            echo $record->PHPUnit_Framework_TestSuite;
//        endforeach;
        //var_dump($suiteData);
        return (
            (strpos($suiteData, self::PHPUNIT_TEST_SUITE_NAME) != false)
            //and
            //(strpos($suiteData, self::PHPUNIT_TEST_SUITE_DATAPROVIDER_NAME) != false)
            );
        //return true;
    }
}