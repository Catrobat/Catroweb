<?php

declare(strict_types=1);

namespace App\Project\CatrobatCode\Parser;

use App\Project\CatrobatCode\Parser\Bricks\Brick;
use App\Project\CatrobatCode\Parser\Scripts\Script;
use Symfony\Component\Config\Definition\Exception\Exception;

class CodeStatistic
{
  private int $total_num_scenes = 0;

  private int $total_num_scripts = 0;

  private int $total_num_bricks = 0;

  private int $total_num_objects = 0;

  private int $total_num_looks = 0;

  private int $total_num_sounds = 0;

  private int $total_num_global_vars = 0;

  private int $total_num_local_vars = 0;

  private array $brick_type_statistic = [
    'eventBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'controlBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'motionBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'soundBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'looksBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'penBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'dataBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'deviceBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
    'specialBricks' => [
      'numTotal' => 0,
      'different' => [
        'numDifferent' => 0,
        'listDifferent' => [],
      ],
    ],
  ];

  private array $brick_type_register = [
    'eventBricks' => [],
    'controlBricks' => [],
    'motionBricks' => [],
    'soundBricks' => [],
    'looksBricks' => [],
    'penBricks' => [],
    'dataBricks' => [],
    'deviceBricks' => [],
    'specialBricks' => [],
  ];

  public function update(ParsedObjectsContainer $object_list_container): void
  {
    if ($object_list_container instanceof ParsedScene) {
      $this->updateSceneStatistic();
    }

    $objects = array_merge([$object_list_container->getBackground()], $object_list_container->getObjects());

    foreach ($objects as $object) {
      /* @var ParsedObject|ParsedObjectGroup $this */
      if ($object->isGroup()) {
        foreach ($object->getObjects() as $group_object) {
          $this->updateObjectStatistic($group_object);
        }
      } else {
        $this->updateObjectStatistic($object);
      }
    }
  }

  public function computeVariableStatistic(\SimpleXMLElement $project_xml_properties): void
  {
    $this->countGlobalVariables($project_xml_properties);
    $this->countLocalVariables($project_xml_properties);
  }

  public function getSceneStatistic(): int
  {
    return $this->total_num_scenes;
  }

  public function getScriptStatistic(): int
  {
    return $this->total_num_scripts;
  }

  public function getBrickStatistic(): int
  {
    return $this->total_num_bricks;
  }

  public function getBrickTypeStatistic(): array
  {
    return $this->brick_type_statistic;
  }

  public function getObjectStatistic(): int
  {
    return $this->total_num_objects;
  }

  public function getLookStatistic(): int
  {
    return $this->total_num_looks;
  }

  public function getSoundStatistic(): int
  {
    return $this->total_num_sounds ?? 0;
  }

  public function getGlobalVarStatistic(): int
  {
    return $this->total_num_global_vars ?? 0;
  }

  public function getLocalVarStatistic(): int
  {
    return $this->total_num_local_vars ?? 0;
  }

  protected function updateSceneStatistic(): void
  {
    ++$this->total_num_scenes;
  }

  protected function updateObjectStatistic(ParsedObject $object): void
  {
    ++$this->total_num_objects;

    $this->updateLookStatistic(count($object->getLooks()));
    $this->updateSoundStatistic(count($object->getSounds()));

    foreach ($object->getScripts() as $script) {
      $this->updateScriptStatistic($script);
    }
  }

  protected function updateLookStatistic(int $num_looks): void
  {
    $this->total_num_looks += $num_looks;
  }

  protected function updateSoundStatistic(int $num_sounds): void
  {
    $this->total_num_sounds += $num_sounds;
  }

  protected function updateScriptStatistic(Script $script): void
  {
    ++$this->total_num_scripts;

    $this->updateBrickStatistic($script);

    foreach ($script->getBricks() as $brick) {
      $this->updateBrickStatistic($brick);
    }
  }

  protected function updateBrickStatistic(Brick|Script $brick): void
  {
    ++$this->total_num_bricks;
    switch ($brick->getImgFile()) {
      // Normal Bricks
      case Constants::EVENT_SCRIPT_IMG:
      case Constants::EVENT_BRICK_IMG:
      case Constants::RASPI_EVENT_SCRIPT_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'eventBricks');
        break;
      case Constants::CONTROL_SCRIPT_IMG:
      case Constants::CONTROL_BRICK_IMG:
      case Constants::RASPI_CONTROL_BRICK_IMG:
      case Constants::PHIRO_CONTROL_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'controlBricks');
        break;
      case Constants::MOTION_SCRIPT_IMG:
      case Constants::MOTION_BRICK_IMG:
      case Constants::JUMPING_SUMO_BRICK_IMG:
      case Constants::AR_DRONE_MOTION_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'motionBricks');
        break;
      case Constants::SOUND_BRICK_IMG:
      case Constants::PHIRO_SOUND_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'soundBricks');
        break;
      case Constants::LOOKS_BRICK_IMG:
      case Constants::AR_DRONE_LOOKS_BRICK_IMG:
      case Constants::PHIRO_LOOK_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'looksBricks');
        break;
      case Constants::PEN_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'penBricks');
        break;
      case Constants::DATA_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'dataBricks');
        break;
      case Constants::DEVICE_BRICK_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'deviceBricks');
        break;
      case Constants::UNKNOWN_BRICK_IMG:
      case Constants::DEPRECATED_BRICK_IMG:
      case Constants::UNKNOWN_SCRIPT_IMG:
      case Constants::DEPRECATED_SCRIPT_IMG:
      case Constants::LEGO_EV3_BRICK_IMG:
      case Constants::LEGO_NXT_BRICK_IMG:
      case Constants::ARDUINO_BRICK_IMG:
      case Constants::EMBROIDERY_BRICK_IMG:
      case Constants::RASPI_BRICK_IMG:
      case Constants::PHIRO_BRICK_IMG:
      case Constants::TESTING_BRICK_IMG:
      case Constants::YOUR_BRICK_IMG:
      case Constants::YOUR_SCRIPT_IMG:
        $this->updateBrickTypeStatistic($brick->getType(), 'specialBricks');
        break;
    }
  }

  protected function updateBrickTypeStatistic(mixed $brick_type, mixed $brick_category): void
  {
    ++$this->brick_type_statistic[$brick_category]['numTotal'];
    if (!in_array($brick_type, $this->brick_type_register[$brick_category], true)) {
      ++$this->brick_type_statistic[$brick_category]['different']['numDifferent'];
      $this->brick_type_statistic[$brick_category]['different']['listDifferent'][] = $brick_type;
      $this->brick_type_register[$brick_category][] = $brick_type;
    }
  }

  protected function countGlobalVariables(\SimpleXMLElement $project_xml_properties): void
  {
    try {
      $this->total_num_global_vars =
        count($project_xml_properties->xpath('//programVariableList//userVariable')) +
        count($project_xml_properties->xpath('//programListOfLists//userVariable'));
    } catch (Exception) {
      $this->total_num_global_vars = 0;
    }
  }

  protected function countLocalVariables(\SimpleXMLElement $project_xml_properties): void
  {
    try {
      $this->total_num_local_vars =
        count($project_xml_properties->xpath('//userVariable//userVariable')) - $this->total_num_global_vars;

      if ($this->total_num_local_vars <= 0) {
        // might be a old project using the old deprecated format to define local variables
        $this->total_num_local_vars =
          count($project_xml_properties->xpath('//objectListOfList//userVariable')) +
          count($project_xml_properties->xpath('//objectVariableList//userVariable'));
      }
    } catch (Exception) {
      $this->total_num_local_vars = 0;
    }
  }
}
