<?php
namespace chartinger\Behat\TwigReportExtension\facades;

interface StepInterface
{

    public function getText();

    public function getResult();

    public function getArguments();

    public function getLine();
}