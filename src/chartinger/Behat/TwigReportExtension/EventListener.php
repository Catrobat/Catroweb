<?php
namespace chartinger\Behat\TwigReportExtension;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;
use Behat\Behat\EventDispatcher\Event\FeatureTested;
use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;
use Behat\Testwork\EventDispatcher\Event\SuiteTested;
use Behat\Behat\EventDispatcher\Event\StepTested;
use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Behat\EventDispatcher\Event\BackgroundTested;
use Behat\Behat\EventDispatcher\Event\AfterBackgroundTested;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use chartinger\Behat\TwigReportExtension\facades\Step;
use chartinger\Behat\TwigReportExtension\facades\Feature;
use chartinger\Behat\TwigReportExtension\facades\Background;
use chartinger\Behat\TwigReportExtension\facades\Scenario;
use chartinger\Behat\TwigReportExtension\facades\OutlineScenario;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Behat\Testwork\EventDispatcher\Event\AfterExerciseCompleted;

class EventListener implements EventSubscriberInterface
{

    private $templating;

    private $template;

    private $output_directory;

    private $features = array();

    private $scenarios = array();

    private $background = null;

    private $steps = array();

    private $statistics = array();

    public function __construct(\Twig_Environment $templating)
    {
        $this->templating = $templating;
        $this->counter = 0;
        $this->resetStats();
    }

    private function resetStats()
    {
        $empty = array(
            "total" => 0,
            "passed" => 0,
            "failed" => 0,
            "skipped" => 0,
            "pending" => 0
        );
        $this->statistics = array(
            'features' => $empty,
            'scenarios' => $empty,
            'steps' => $empty
        );
    }

    static public function getSubscribedEvents()
    {
        return array(
            StepTested::AFTER => 'afterStep',
            FeatureTested::AFTER => 'afterFeature',
            ScenarioTested::AFTER => 'afterScenario',
            SuiteTested::AFTER => 'afterSuite',
            BackgroundTested::AFTER => 'afterBackground',
            OutlineTested::AFTER => 'afterOutline',
            ExerciseCompleted::AFTER => 'afterExercise'
        );
    }

    public function afterStep(AfterStepTested $event)
    {
        $this->steps[] = new Step($event);
        $this->updateStats("steps", $event->getTestResult()
            ->getResultCode());
    }

    public function afterScenario(AfterScenarioTested $event)
    {
        $this->scenarios[] = new Scenario($event, $this->steps);
        $this->updateStats("scenarios", $event->getTestResult()
            ->getResultCode());
        $this->steps = array();
    }

    public function afterOutline(AfterOutlineTested $event)
    {
        $this->scenarios[] = new OutlineScenario($event, $this->steps);
        $this->updateStats("scenarios", $event->getTestResult()
            ->getResultCode());
        $this->steps = array();
    }

    public function afterFeature(AfterFeatureTested $event)
    {
        $this->updateStats("features", $event->getTestResult()
            ->getResultCode());
        $this->features[] = new Feature($event, $this->scenarios, $this->background);
        $this->scenarios = array();
        $this->background = null;
    }

    public function afterBackground(AfterBackgroundTested $event)
    {
        $this->background = new Background($event, $this->steps);
        $background_steps = count($event->getBackground()->getSteps());
        $this->steps = array_slice($this->steps, 0, count($this->steps) - $background_steps);
    }

    public function afterSuite(SuiteTested $event)
    {
        if ($this->output_directory) {
            $suite_name = ($event->getEnvironment()
                ->getSuite()
                ->getName());
            
            $features = $this->features;
            
            $rendered = $this->templating->render($this->template, array(
                'features' => $features,
                'statistics' => $this->statistics,
                'suites' => $suite_name
            ));
            
            file_put_contents($this->output_directory . "/" . $suite_name . ".html", $rendered);
            
            $this->features = array();
            $this->scenarios = array();
            $this->background = null;
            $this->steps = array();
            
            $this->resetStats();
        }
    }

    public function afterExercise(ExerciseCompleted $event)
    {
    }

    private function updateStats($category, $result)
    {
        switch ($result) {
            case TestResult::PASSED:
                $this->statistics[$category]["passed"] ++;
                break;
            case TestResult::FAILED:
                $this->statistics[$category]["failed"] ++;
                break;
            case TestResult::PENDING:
                $this->statistics[$category]["pending"] ++;
                break;
            default:
                $this->statistics[$category]["skipped"] ++;
        }
        $this->statistics[$category]["total"] ++;
    }

    public function setTemplate($file)
    {
        $this->template = $file;
    }

    public function setOutputDirectory($directory)
    {
        $this->output_directory = $directory;
    }
}