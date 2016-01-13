<?php
namespace Tests;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

class CodeExtractor extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function xmlWithIfAndCondition()
    {
        $this->checkFiles('/Resources/CodeXmls/ifWithCondition/');
    }

    /**
     * @test
     */
    public function xmlWithFormula()
    {
        $this->checkFiles('/Resources/CodeXmls/formula/');
    }

    /**
     * @test
     */
    public function xmlWithIf()
    {
        $this->checkFiles('/Resources/CodeXmls/if/');
    }

    /**
     * @test
     */
    public function xmlWithLoop()
    {
        $this->checkFiles('/Resources/CodeXmls/loop/');
    }

    /**
     * @test
     */
    public function xmlWithAllBricks()
    {
        $this->checkFiles('/Resources/CodeXmls/allBricks/');
    }


    /**
     * @test
     */
    public function xmlWithNestedFormula()
    {
        $this->checkFiles('/Resources/CodeXmls/nestedFormula/');
    }


    /**
     * @test
     */
    public function xmlWithNestedObjects()
    {
        $this->checkFiles('/Resources/CodeXmls/nestedObjects/');
    }


    private function checkFiles($path){
        $output = $this->getCode($path);
        $referenceOutput = $this->readReferenceOutputFile($path);
        $this->assertEquals($referenceOutput, $output);
    }

    private function getCode($path){
        $eCFile = new ExtractedCatrobatFile(__DIR__ . $path, '', '');

        $objects = $eCFile->getCodeObjects();

        return $this->generateCode($objects);
    }

    private function generateCode($objects){
        $code = '';
        foreach($objects as $object)
        {
            $code .= $object->getCode();
            $code .= $this->generateCode($object->getCodeObjects());

        }
        $code = str_replace("&nbsp;", " ", $code);
        $code = str_replace("<br/>", PHP_EOL, $code);
        return $code;
    }

    private function readReferenceOutputFile($path){
        $absolutePath = __DIR__ . $path . 'reference.output';
        $referenceOutputFile = fopen($absolutePath , "r") or die('Unable to open file!');
        $referenceOutput = fread($referenceOutputFile,filesize($absolutePath));
        fclose($referenceOutputFile);
        return $referenceOutput;
    }

}
