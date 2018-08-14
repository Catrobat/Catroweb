<?php

namespace Tests\CatrobatCodeParserTests;

use Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject;
use Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObjectGroup;

class ParsedObjectGroupTest extends \PHPUnit\Framework\TestCase
{
    protected $group;

    public function setUp()
    {
        $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
        $this->group = new ParsedObjectGroup($xml_properties->xpath('//object[@type="GroupSprite"]')[0]);
    }

    /**
     * @test
     * @dataProvider provideMethodNames
     */
    public function mustHaveMethod($method_name)
    {
        $this->assertTrue(method_exists($this->group, $method_name));
    }

    public function provideMethodNames()
    {
        return array(
          ['getName'],
          ['addObject'],
          ['getObjects'],
          ['isGroup']
        );
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function isGroupMustReturnTrue()
    {
        $this->assertTrue($this->group->isGroup());
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getObjectsMustReturnArrayOfParsedObject()
    {
        $expected = 'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject';

        foreach($this->group->getObjects() as $actual)
            $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function addObjectMustAddObjectToObjects()
    {
        $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
        $this->group->addObject(new ParsedObject($xml_properties->xpath('//object')[0]));
        $this->assertNotEmpty($this->group->getObjects());
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getNameMustReturnCertainString()
    {
        $expected = 'TestGroup';
        $actual = $this->group->getName();

        $this->assertEquals($expected, $actual);
    }
}