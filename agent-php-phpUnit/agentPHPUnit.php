<?php
/**
 * Created by PhpStorm.
 * User: Mikalai_Kabzar
 * Date: 1/9/2018
 * Time: 1:47 PM
 */
use PHPUnit\Framework as Framework;
use ReportPortalBasic\Enum\ItemStatusesEnum as ItemStatusesEnum;
use ReportPortalBasic\Enum\ItemTypesEnum as ItemTypesEnum;
use ReportPortalBasic\Enum\LogLevelsEnum as LogLevelsEnum;
use ReportPortalBasic\Service\ReportPortalHTTPService;
use GuzzleHttp\Psr7\Response as Response;

class agentPHPUnit implements Framework\TestListener
{
    const PHPUNIT_TEST_SUITE_NAME = 'PHPUnit\Framework\TestSuite';
    const PHPUNIT_TEST_SUITE_DATAPROVIDER_NAME = 'PHPUnit\Framework\DataProviderTestSuite';

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

    private static $isFirstSuite = true;

    /**
     * @var ReportPortalHTTPService
     */
    protected static $httpService;

    /**
     * agentPHPUnit constructor.
     * @param $UUID
     * @param $host
     * @param $projectName
     * @param $timeZone
     * @param $launchName
     * @param $launchDescription
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
     * @param $test
     * @return null|string
     */
    private function getTestStatus($test)
    {
        $status = $test->getStatus();
        $statusResult = null;
        if ($status == PHPUnit\Runner\BaseTestRunner::STATUS_PASSED) {
            $statusResult = ItemStatusesEnum::PASSED;
        } else if ($status == PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE) {
            $statusResult = ItemStatusesEnum::FAILED;
        } else if ($status == PHPUnit\Runner\BaseTestRunner::STATUS_SKIPPED) {
            $statusResult = ItemStatusesEnum::SKIPPED;
        } else if ($status == PHPUnit\Runner\BaseTestRunner::STATUS_INCOMPLETE) {
            $statusResult = ItemStatusesEnum::STOPPED;
        } else if ($status == PHPUnit\Runner\BaseTestRunner::STATUS_ERROR) {
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
     * @param Framework\Test $test
     * @param Framework\Exception $e
     * @param $logLevelsEnum
     * @param $testItemID
     */
    private function addSetOfLogMessages(PHPUnit\Framework\Test $test, PHPUnit\Framework\Exception $e, $logLevelsEnum, $testItemID)
    {
        $errorMessage = $e->toString();
        self::$httpService->addLogMessage($testItemID, $errorMessage, $logLevelsEnum);

        $this->AddLogMessages($test, $e, $logLevelsEnum, $testItemID);

        $trace = $e->getTraceAsString();
        self::$httpService->addLogMessage($testItemID, $trace, $logLevelsEnum);
    }

    /**
     * @param Framework\Test $test
     * @param Framework\AssertionFailedError $e
     * @param $logLevelsEnum
     * @param $testItemID
     */
    private function AddLogMessages(PHPUnit\Framework\Test $test, PHPUnit\Framework\AssertionFailedError $e, $logLevelsEnum, $testItemID)
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

    /**
     * @param bool $isFailedItem
     * @return string
     */
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
     * @param Framework\TestSuite $suite
     * @return bool
     */
    private static function isRealSuite(PHPUnit\Framework\TestSuite $suite)
    {
       $suiteData = var_export($suite->tests(), true);
       //if (self::$isFirstSuite) {
       //    echo $suiteData;
       //}
        self::$isFirstSuite = true;
       return ((strpos($suiteData, self::PHPUNIT_TEST_SUITE_NAME) != false) and ((strpos($suiteData, self::PHPUNIT_TEST_SUITE_DATAPROVIDER_NAME) != false)));
    }

    /**
     * A warning occurred.
     * @param Framework\Test $test
     * @param Framework\Warning $e
     * @param float $time
     */
    public function addWarning(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\Warning $e, float $time): void
    {
        // TODO: Implement addWarning() method.
    }

    /**
     * Risky test.
     * @param Framework\Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addRiskyTest(\PHPUnit\Framework\Test $test, \Throwable $t, float $time): void
    {
        // TODO: Implement addRiskyTest() method.
    }

    /**
     * An error occurred.
     * @param Framework\Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addError(\PHPUnit\Framework\Test $test, \Throwable $t, float $time): void
    {
        $this->addSetOfLogMessages($test, $t, LogLevelsEnum::FATAL, $this->testItemID);
    }

    /**
     * A test ended.
     * @param Framework\Test $test
     * @param float $time
     */
    public function endTest(\PHPUnit\Framework\Test $test, float $time): void
    {
        $testStatus = $this->getTestStatus($test);
        self::$httpService->finishItem($this->testItemID, $testStatus, $time . ' seconds');
    }

    /**
     * A test started.
     * @param Framework\Test $test
     */
    public function startTest(\PHPUnit\Framework\Test $test): void
    {
        $this->testName = $test->getName();
        $this->testDescription = '';
        $response = self::$httpService->startChildItem($this->classItemID, $this->testDescription, $this->testName, ItemTypesEnum::TEST, []);
        $this->testItemID = self::getID($response);
    }

    /**
     * A test suite ended.
     * @param Framework\TestSuite $suite
     */
    public function endTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        if (!self::isRealSuite($suite)) {
            self::$httpService->finishItem($this->classItemID, ItemStatusesEnum::FAILED, $this->classDescription);
        }
        if (self::isRealSuite($suite)) {
            self::$httpService->finishRootItem();
        }
    }

    /**
     * A test suite started.
     * @param Framework\TestSuite $suite
     */
    public function startTestSuite(\PHPUnit\Framework\TestSuite $suite): void
    {
        //$iterator = $suite->getName();
        //var_dump($iterator);
        //$iterator = $suite->getGroupDetails();
        //var_dump($iterator);
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
     * A failure occurred.
     * @param Framework\Test $test
     * @param Framework\AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(\PHPUnit\Framework\Test $test, \PHPUnit\Framework\AssertionFailedError $e, float $time): void
    {
        $this->addSetOfLogMessages($test, $e, LogLevelsEnum::ERROR, $this->testItemID);
    }

    /**
     * Skipped test.
     * @param Framework\Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addSkippedTest(\PHPUnit\Framework\Test $test, \Throwable $t, float $time): void
    {
        $this->addSetOfLogMessages($test, $t, LogLevelsEnum::WARN, $this->testItemID);
    }

    /**
     * Incomplete test.
     * @param Framework\Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addIncompleteTest(\PHPUnit\Framework\Test $test, \Throwable $t, float $time): void
    {
        $this->addSetOfLogMessages($test, $t, LogLevelsEnum::WARN, $this->testItemID);
    }
}