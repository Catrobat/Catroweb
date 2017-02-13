<?php

namespace Tests\CatrobatCodeParserTests;

use Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject;

class ParsedObjectTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
        $this->object = new ParsedObject($xml_properties->xpath('//object')[0]);
    }

    /**
     * @test
     * @dataProvider provideMethodNames
     */
    public function mustHaveMethod($method_name)
    {
        $this->assertTrue(method_exists($this->object, $method_name));
    }

    public function provideMethodNames()
    {
        return array(
          ['getName'],
          ['getScripts'],
          ['getSounds'],
          ['getLooks'],
          ['isGroup']
        );
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function isGroupMustReturnFalse()
    {
        $this->assertFalse($this->object->isGroup());
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getLooksMustReturnArrayOfParsedObjectAsset()
    {
        $expected = 'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObjectAsset';

        foreach($this->object->getLooks() as $actual)
            $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getSoundsMustReturnArrayOfParsedObjectAsset()
    {
        $expected = 'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObjectAsset';

        foreach($this->object->getSounds() as $actual)
            $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getScriptsMustReturnArrayOfScript()
    {
        $expected = 'Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts\Script';

        foreach($this->object->getScripts() as $actual)
            $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getNameMustReturnCertainString()
    {
        $expected = 'Background';
        $actual = $this->object->getName();

        $this->assertEquals($expected, $actual);
    }
}