<?php

namespace Catrobat\Behat\TwigReportExtension;

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
use Behat\Behat\EventDispatcher\Event\OutlineTested;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;
use Catrobat\Behat\TwigReportExtension\facades\Step;
use Catrobat\Behat\TwigReportExtension\facades\Feature;
use Catrobat\Behat\TwigReportExtension\facades\Background;
use Catrobat\Behat\TwigReportExtension\facades\Scenario;
use Catrobat\Behat\TwigReportExtension\facades\OutlineScenario;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class EventListener
 * @package Catrobat\Behat\TwigReportExtension
 */
class EventListener implements EventSubscriberInterface
{

  /**
   * @var \Twig_Environment
   */
  private $templating;

  /**
   * @var
   */
  private $template;

  /**
   * @var
   */
  private $index_filename;

  /**
   * @var
   */
  private $index_template;

  /**
   * @var
   */
  private $output_directory;

  /**
   * @var
   */
  private $extension;

  /**
   * @var
   */
  private $scope;

  /**
   * @var array
   */
  private $features = [];

  /**
   * @var array
   */
  private $scenarios = [];

  /**
   * @var
   */
  private $background = null;

  /**
   * @var array
   */
  private $steps = [];

  /**
   * @var array
   */
  private $statistics = [];

  /**
   * @var int
   */
  private $counter;


  /**
   * EventListener constructor.
   *
   * @param \Twig_Environment $templating
   */
  public function __construct(\Twig_Environment $templating)
  {
    $this->templating = $templating;
    $this->counter = 0;
    $this->resetStats();
  }

  /**
   *
   */
  private function resetStats()
  {
    $empty = [
      "total"   => 0,
      "passed"  => 0,
      "failed"  => 0,
      "skipped" => 0,
      "pending" => 0,
    ];
    $this->statistics = [
      'features'  => $empty,
      'scenarios' => $empty,
      'steps'     => $empty,
    ];
  }

  /**
   * @return array
   */
  static public function getSubscribedEvents()
  {
    return [
      StepTested::AFTER        => 'afterStep',
      FeatureTested::AFTER     => 'afterFeature',
      ScenarioTested::AFTER    => 'afterScenario',
      SuiteTested::AFTER       => 'afterSuite',
      BackgroundTested::AFTER  => 'afterBackground',
      OutlineTested::AFTER     => 'afterOutline',
      ExerciseCompleted::AFTER => 'afterExercise',
    ];
  }

  /**
   * @param AfterStepTested $event
   */
  public function afterStep(AfterStepTested $event)
  {
    $this->steps[] = new Step($event);
    $this->updateStats("steps", $event->getTestResult()
      ->getResultCode());
  }

  /**
   * @param AfterScenarioTested $event
   */
  public function afterScenario(AfterScenarioTested $event)
  {
    $this->scenarios[] = new Scenario($event, $this->steps);
    $this->updateStats("scenarios", $event->getTestResult()
      ->getResultCode());
    $this->steps = [];
  }

  /**
   * @param AfterOutlineTested $event
   */
  public function afterOutline(AfterOutlineTested $event)
  {
    $this->scenarios[] = new OutlineScenario($event, $this->steps);
    $this->updateStats("scenarios", $event->getTestResult()
      ->getResultCode());
    $this->steps = [];
  }

  /**
   * @param AfterFeatureTested $event
   *
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Runtime
   * @throws \Twig_Error_Syntax
   */
  public function afterFeature(AfterFeatureTested $event)
  {
    $this->updateStats("features", $event->getTestResult()
      ->getResultCode());
    $feature = new Feature($event, $this->scenarios, $this->background);

    if ($this->scope == "feature")
    {
      $rendered = $this->templating->render($this->template, [
        'feature' => $feature,
      ]);
      $featurefile = new File($feature->getFile());
      $filename = $featurefile->getBasename(".feature");
      file_put_contents($this->output_directory . "/" . $filename . "." . $this->extension, $rendered);
    }

    $this->features[] = $feature;
    $this->scenarios = [];
    $this->background = null;
  }

  /**
   * @param AfterBackgroundTested $event
   */
  public function afterBackground(AfterBackgroundTested $event)
  {
    $this->background = new Background($event, $this->steps);
    $background_steps = count($event->getBackground()->getSteps());
    $this->steps = array_slice($this->steps, 0, count($this->steps) - $background_steps);
  }

  /**
   * @param SuiteTested $event
   *
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Runtime
   * @throws \Twig_Error_Syntax
   */
  public function afterSuite(SuiteTested $event)
  {
    if ($this->output_directory)
    {
      $suite_name = ($event->getEnvironment()
        ->getSuite()
        ->getName());

      $features = $this->features;

      if ($this->scope == "suite")
      {
        $rendered = $this->templating->render($this->template, [
          'features'   => $features,
          'statistics' => $this->statistics,
          'suites'     => $suite_name,
        ]);
        file_put_contents($this->output_directory . "/" . $suite_name . "." . $this->extension, $rendered);
      }

      if (($this->scope == "feature") && ($this->index_filename != null))
      {
        $feature_overviews = [];
        foreach ($this->features as $feature)
        {
          $featurefile = new File($feature->getFile());
          $filename = $featurefile->getBasename(".feature") . "." . $this->extension;
          $feature_overviews[] = ['title' => $feature->getTitle(), 'filename' => $filename, 'description' => $feature->getDescription()];
        }
        $rendered = $this->templating->render($this->index_template, [
          'features' => $feature_overviews,
        ]);
        file_put_contents($this->output_directory . "/" . $this->index_filename . "." . $this->extension, $rendered);
      }

      $this->features = [];
      $this->scenarios = [];
      $this->background = null;
      $this->steps = [];

      $this->resetStats();
    }
  }

  /**
   * @param ExerciseCompleted $event
   */
  public function afterExercise(ExerciseCompleted $event)
  {
  }

  /**
   * @param $category
   * @param $result
   */
  private function updateStats($category, $result)
  {
    switch ($result)
    {
      case TestResult::PASSED:
        $this->statistics[$category]["passed"]++;
        break;
      case TestResult::FAILED:
        $this->statistics[$category]["failed"]++;
        break;
      case TestResult::PENDING:
        $this->statistics[$category]["pending"]++;
        break;
      default:
        $this->statistics[$category]["skipped"]++;
    }
    $this->statistics[$category]["total"]++;
  }

  /**
   * @param $file
   */
  public function setTemplate($file)
  {
    $this->template = $file;
  }

  /**
   * @param $directory
   */
  public function setOutputDirectory($directory)
  {
    $this->output_directory = $directory;
  }

  /**
   * @param $extension
   */
  public function setExtension($extension)
  {
    $this->extension = $extension;
  }

  /**
   * @param $scope
   */
  public function setScope($scope)
  {
    $this->scope = $scope;
  }

  /**
   * @param $template
   */
  public function setIndexTemplate($template)
  {
    $this->index_template = $template;
  }

  /**
   * @param $filename
   */
  public function setIndexFilename($filename)
  {
    $this->index_filename = $filename;
  }
}