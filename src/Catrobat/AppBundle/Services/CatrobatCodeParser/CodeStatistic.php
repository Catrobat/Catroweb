<?php

namespace Catrobat\AppBundle\Services\CatrobatCodeParser;

use Catrobat\AppBundle\Services\CatrobatCodeParser\Scripts\Script;
use Symfony\Component\Config\Definition\Exception\Exception;

class CodeStatistic
{
    private $total_num_scripts;
    private $total_num_bricks;
    private $total_num_objects;
    private $total_num_looks;
    private $total_num_sounds;
    private $total_num_global_vars;
    private $total_num_local_vars;

    private $brick_type_statistic;
    private $brick_type_register;

    public function __construct()
    {
        $this->total_num_scripts = 0;
        $this->total_num_bricks = 0;
        $this->total_num_objects = 0;
        $this->total_num_looks = 0;
        $this->total_num_sounds = 0;
        $this->total_num_global_vars = 0;
        $this->total_num_local_vars = 0;

        $this->brick_type_statistic = array(
          'eventBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          ),
          'controlBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          ),
          'motionBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          ),
          'soundBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          ),
          'looksBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          ),
          'penBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          ),
          'dataBricks' => array(
            'numTotal' => 0,
            'numDifferent' => 0
          )
        );

        $this->brick_type_register = array(
            'eventBricks' => array(),
            'controlBricks' => array(),
            'motionBricks' => array(),
            'soundBricks' => array(),
            'looksBricks' => array(),
            'penBricks' => array(),
            'dataBricks' => array()
        );
    }

    public function update(ParsedObjectsContainer $object_list_container)
    {
        $objects = array_merge(array($object_list_container->getBackground()), $object_list_container->getObjects());

        foreach($objects as $object) {
            if ($object->isGroup())
                foreach($object->getObjects() as $group_object)
                    $this->updateObjectStatistic($group_object);
            else
                $this->updateObjectStatistic($object);
        }
    }

    protected function updateObjectStatistic(ParsedObject $object)
    {
        $this->total_num_objects++;

        $this->updateLookStatistic(count($object->getLooks()));
        $this->updateSoundStatistic(count($object->getSounds()));

        foreach($object->getScripts() as $script)
            $this->updateScriptStatistic($script);
    }

    protected function updateLookStatistic($num_looks)
    {
        $this->total_num_looks += $num_looks;
    }

    protected function updateSoundStatistic($num_sounds)
    {
        $this->total_num_sounds += $num_sounds;
    }

    protected function updateScriptStatistic(Script $script)
    {
        $this->total_num_scripts++;

        $this->updateBrickStatistic($script);

        foreach($script->getBricks() as $brick)
            $this->updateBrickStatistic($brick);
    }

    protected function updateBrickStatistic($brick)
    {
        $this->total_num_bricks++;
        switch ($brick->getImgFile())
        {
            // Normal Bricks
            case Constants::EVENT_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'eventBricks');
                break;
            case Constants::CONTROL_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'controlBricks');
                break;
            case Constants::MOTION_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'motionBricks');
                break;
            case Constants::SOUND_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'soundBricks');
                break;
            case Constants::LOOKS_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'looksBricks');
                break;
            case Constants::PEN_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'penBricks');
                break;
            case Constants::DATA_BRICK_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'dataBricks');
                break;

            // Script Bricks
            case Constants::EVENT_SCRIPT_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'eventBricks');
                break;
            case Constants::CONTROL_SCRIPT_IMG:
                $this->updateBrickTypeStatistic($brick->getType(), 'controlBricks');
                break;
        }
    }

    protected function updateBrickTypeStatistic($brick_type, $brick_category)
    {
        $this->brick_type_statistic[$brick_category]['numTotal']++;
        if (!in_array($brick_type, $this->brick_type_register[$brick_category]))
        {
            $this->brick_type_statistic[$brick_category]['numDifferent']++;
            $this->brick_type_register[$brick_category][] = $brick_type;
        }
    }


    public function computeVariableStatistic(\SimpleXMLElement $program_xml_properties)
    {
        $this->countGlobalVariables($program_xml_properties);
        $this->countLocalVariables($program_xml_properties);
    }

    protected function countGlobalVariables(\SimpleXMLElement $program_xml_properties)
    {
        try
        {
            $this->total_num_global_vars =
              count($program_xml_properties->xpath('//programVariableList//userVariable')) +
              count($program_xml_properties->xpath('//programListOfLists//userVariable'));
        }
        catch(Exception $e)
        {
            $this->total_num_global_vars = null;
        }
    }

    protected function countLocalVariables(\SimpleXMLElement $program_xml_properties)
    {
        try
        {
            $this->total_num_local_vars =
              count($program_xml_properties->xpath('//objectListOfList//userVariable')) +
              count($program_xml_properties->xpath('//objectVariableList//userVariable'));
        }
        catch(Exception $e)
        {
            $this->total_num_local_vars = null;
        }
    }

    public function getScriptStatistic()
    {
        return $this->total_num_scripts;
    }

    public function getBrickStatistic()
    {
        return $this->total_num_bricks;
    }

    public function getBrickTypeStatistic()
    {
        return $this->brick_type_statistic;
    }

    public function getObjectStatistic()
    {
        return $this->total_num_objects;
    }

    public function getLookStatistic()
    {
        return $this->total_num_looks;
    }

    public function getSoundStatistic()
    {
        return $this->total_num_sounds;
    }

    public function getGlobalVarStatistic()
    {
        return $this->total_num_global_vars;
    }

    public function getLocalVarStatistic()
    {
        return $this->total_num_local_vars;
    }
}