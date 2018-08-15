<?php

namespace Tests\CatrobatCodeParserTests;

use Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedScene;

class ParsedObjectsContainerTest extends \PHPUnit\Framework\TestCase
{
    protected $container;

    public function setUp()
    {
        $xml_properties = simplexml_load_file(__DIR__ . '/Resources/ValidPrograms/AllBricksProgram/code.xml');
        $this->container = new ParsedScene($xml_properties->xpath('//scene')[0]);
    }

    /**
     * @test
     * @dataProvider provideMethodNames
     */
    public function mustHaveMethod($method_name)
    {
        $this->assertTrue(method_exists($this->container, $method_name));
    }

    public function provideMethodNames()
    {
        return array(
          ['getObjects'],
          ['getBackground']
        );
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getBackgroundMustReturnParsedObject()
    {
        $actual = $this->container->getBackground();
        $expected = 'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject';

        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @depends mustHaveMethod
     */
    public function getObjectsMustReturnArrayOfParsedObjectOrParsedObjectGroup()
    {
        $expected = array(
            'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObject',
            'Catrobat\AppBundle\Services\CatrobatCodeParser\ParsedObjectGroup'
        );

        foreach($this->container->getObjects() as $actual)
            $this->assertThat($actual, $this->logicalOr(
                $this->isInstanceOf($expected[0]),
                $this->isInstanceOf($expected[1])
            ));
    }

    /**
     * @test
     */
    public function mustThrowExceptionIfCorruptedGroup()
    {
        $this->expectExceptionMessage('\Exception');

        $xml_properties = simplexml_load_file(__DIR__
            . '/Resources/FaultyPrograms/CorruptedGroupFaultyProgram/code.xml');

        var_export("\n\n".$xml_properties->xpath('//scene')[0]."\n\n");
        
        new ParsedScene($xml_properties->xpath('//scene')[0]);
    }
}