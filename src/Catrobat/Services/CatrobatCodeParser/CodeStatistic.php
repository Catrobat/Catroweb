<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use App\Catrobat\Services\CatrobatCodeParser\Scripts\Script;
use Symfony\Component\Config\Definition\Exception\Exception;


/**
 * Class CodeStatistic
 * @package App\Catrobat\Services\CatrobatCodeParser
 */
class CodeStatistic
{
  /**
   * @var int
   */
  private $total_num_scenes;
  /**
   * @var int
   */
  private $total_num_scripts;
  /**
   * @var int
   */
  private $total_num_bricks;
  /**
   * @var int
   */
  private $total_num_objects;
  /**
   * @var int
   */
  private $total_num_looks;
  /**
   * @var int
   */
  private $total_num_sounds;
  /**
   * @var int
   */
  private $total_num_global_vars;
  /**
   * @var int
   */
  private $total_num_local_vars;

  /**
   * @var array
   */
  private $brick_type_statistic;
  /**
   * @var array
   */
  private $brick_type_register;

  /**
   * CodeStatistic constructor.
   */
  public function __construct()
  {
    $this->total_num_scenes = 0;
    $this->total_num_scripts = 0;
    $this->total_num_bricks = 0;
    $this->total_num_objects = 0;
    $this->total_num_looks = 0;
    $this->total_num_sounds = 0;
    $this->total_num_global_vars = 0;
    $this->total_num_local_vars = 0;

    $this->brick_type_statistic = [
      'eventBricks'   => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
      'controlBricks' => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
      'motionBricks'  => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
      'soundBricks'   => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
      'looksBricks'   => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
      'penBricks'     => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
      'dataBricks'    => [
        'numTotal'  => 0,
        'different' => [
          'numDifferent'  => 0,
          'listDifferent' => [],
        ],
      ],
    ];

    $this->brick_type_register = [
      'eventBricks'   => [],
      'controlBricks' => [],
      'motionBricks'  => [],
      'soundBricks'   => [],
      'looksBricks'   => [],
      'penBricks'     => [],
      'dataBricks'    => [],
    ];
  }

  /**
   * @param ParsedObjectsContainer $object_list_container
   */
  public function update(ParsedObjectsContainer $object_list_container)
  {
    if ($object_list_container instanceof ParsedScene)
    {
      $this->updateSceneStatistic();
    }

    $objects = array_merge([$object_list_container->getBackground()], $object_list_container->getObjects());

    foreach ($objects as $object)
    {
      /**
       * @var $object ParsedObject|ParsedObjectGroup
       */
      if ($object->isGroup())
      {
        foreach ($object->getObjects() as $group_object)
          $this->updateObjectStatistic($group_object);
      }
      else
      {
        $this->updateObjectStatistic($object);
      }
    }
  }

  /**
   *
   */
  protected function updateSceneStatistic()
  {
    $this->total_num_scenes++;
  }

  /**
   * @param ParsedObject $object
   */
  protected function updateObjectStatistic(ParsedObject $object)
  {
    $this->total_num_objects++;

    $this->updateLookStatistic(count($object->getLooks()));
    $this->updateSoundStatistic(count($object->getSounds()));

    foreach ($object->getScripts() as $script)
      $this->updateScriptStatistic($script);
  }

  /**
   * @param $num_looks
   */
  protected function updateLookStatistic($num_looks)
  {
    $this->total_num_looks += $num_looks;
  }

  /**
   * @param $num_sounds
   */
  protected function updateSoundStatistic($num_sounds)
  {
    $this->total_num_sounds += $num_sounds;
  }

  /**
   * @param Script $script
   */
  protected function updateScriptStatistic(Script $script)
  {
    $this->total_num_scripts++;

    $this->updateBrickStatistic($script);

    foreach ($script->getBricks() as $brick)
      $this->updateBrickStatistic($brick);
  }

  /**
   * @param $brick Script
   */
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

  /**
   * @param $brick_type
   * @param $brick_category
   */
  protected function updateBrickTypeStatistic($brick_type, $brick_category)
  {
    $this->brick_type_statistic[$brick_category]['numTotal']++;
    if (!in_array($brick_type, $this->brick_type_register[$brick_category]))
    {
      $this->brick_type_statistic[$brick_category]['different']['numDifferent']++;
      $this->brick_type_statistic[$brick_category]['different']['listDifferent'][] = $brick_type;
      $this->brick_type_register[$brick_category][] = $brick_type;
    }
  }


  /**
   * @param \SimpleXMLElement $program_xml_properties
   */
  public function computeVariableStatistic(\SimpleXMLElement $program_xml_properties)
  {
    $this->countGlobalVariables($program_xml_properties);
    $this->countLocalVariables($program_xml_properties);
  }

  /**
   * @param \SimpleXMLElement $program_xml_properties
   */
  protected function countGlobalVariables(\SimpleXMLElement $program_xml_properties)
  {
    try
    {
      $this->total_num_global_vars =
        count($program_xml_properties->xpath('//programVariableList//userVariable')) +
        count($program_xml_properties->xpath('//programListOfLists//userVariable'));
    } catch (Exception $e)
    {
      $this->total_num_global_vars = null;
    }
  }

  /**
   * @param \SimpleXMLElement $program_xml_properties
   */
  protected function countLocalVariables(\SimpleXMLElement $program_xml_properties)
  {
    try
    {
      $this->total_num_local_vars =
        count($program_xml_properties->xpath('//objectListOfList//userVariable')) +
        count($program_xml_properties->xpath('//objectVariableList//userVariable'));
    } catch (Exception $e)
    {
      $this->total_num_local_vars = null;
    }
  }

  /**
   * @return int
   */
  public function getSceneStatistic()
  {
    return $this->total_num_scenes;
  }

  /**
   * @return int
   */
  public function getScriptStatistic()
  {
    return $this->total_num_scripts;
  }

  /**
   * @return int
   */
  public function getBrickStatistic()
  {
    return $this->total_num_bricks;
  }

  /**
   * @return array
   */
  public function getBrickTypeStatistic()
  {
    return $this->brick_type_statistic;
  }

  /**
   * @return int
   */
  public function getObjectStatistic()
  {
    return $this->total_num_objects;
  }

  /**
   * @return int
   */
  public function getLookStatistic()
  {
    return $this->total_num_looks;
  }

  /**
   * @return int
   */
  public function getSoundStatistic()
  {
    return $this->total_num_sounds;
  }

  /**
   * @return int
   */
  public function getGlobalVarStatistic()
  {
    return $this->total_num_global_vars;
  }

  /**
   * @return int
   */
  public function getLocalVarStatistic()
  {
    return $this->total_num_local_vars;
  }
}