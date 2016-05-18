<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

interface ScenarioInterface
{

    public function getTitle();

    public function getResult();

    public function getSteps();

    public function isOutline();

    public function getParameters();

    public function getExamples();
}
