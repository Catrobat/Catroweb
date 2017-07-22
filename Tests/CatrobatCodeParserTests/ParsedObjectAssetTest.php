<?php

namespace Tests\CatrobatCodeParserTests;

use Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObjectAsset;

class ParsedObjectAssetTest extends \PHPUnit_Framework_TestCase
{
    protected $assets;

    public function setUp()
    {
        $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
        $this->assets[] = new ParsedObjectAsset($xml_properties->xpath('//look')[0]);
        $this->assets[] = new ParsedObjectAsset($xml_properties->xpath('//sound')[0]);
    }

    /**
     * @test
     * @dataProvider provideMethodNames
     */
    public function mustHaveMethod($method_name)
    {
        $this->assertTrue(method_exists($this->assets[0], $method_name));
        $this->assertTrue(method_exists($this->assets[1], $method_name));
    }

    public function provideMethodNames()
    {
        return array(
          ['getFileName'],
          ['getName'],
        );
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getFileNameMustReturnCertainString()
    {
        $expected = array(
            'e3b880f6b5eb89981ddb0cf18c545e4d_Mars (Landscape).png',
            '0377a7476136e5e8c780c64a4828922d_AlienCreak1.wav'
        );
        $actual = array(
            $this->assets[0]->getFileName(),
            $this->assets[1]->getFileName()
        );

        $this->assertEquals($expected[0], $actual[0]);
        $this->assertEquals($expected[1], $actual[1]);
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getNameMustReturnCertainString()
    {
        $expected = array(
            'Mars (Landscape)',
            'AlienCreak1'
        );

        $actual = array(
            $this->assets[0]->getName(),
            $this->assets[1]->getName()
        );

        $this->assertEquals($expected[0], $actual[0]);
        $this->assertEquals($expected[1], $actual[1]);
    }
}