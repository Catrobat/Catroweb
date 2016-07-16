<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\CatrobatCode\Statements\FileNameStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LookListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\LookStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\ScriptListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\SoundListStatement;
use Catrobat\AppBundle\CatrobatCode\Statements\UserListStatement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class CodeViewController extends Controller
{
  /**
   * @Route("/program/{id}/codeview", name="show_code_view", requirements={"id":".+"})
   * @Method({"GET"})
   */
  public function codeViewAction($id)
  {
    $program = $this->get('programmanager')->find($id);
    $extracted_file_repository = $this->get('extractedfilerepository');
    $extracted_program = $extracted_file_repository->loadProgramExtractedFile($program);

    $twig_params = $this->computeTwigParams($extracted_program);

    return $this->get('templating')->renderResponse('::codeview.html.twig', $twig_params);
  }

  private function computeTwigParams($extracted_program) {
    $code_objects = $extracted_program->getCodeObjects();
    $twig_params = null;

    if (!empty($code_objects)) {
      $object_list = array();
      foreach ($code_objects as $key => $code_object) {
        if ($key === 0) {
          $background = $this->formatObject($code_object);
        } else {
          $object_list[] = $this->formatObject($code_object);
        }
      }

      /*
      $debug = array();
      $debugtwo = null;

      $code_object = $code_objects[0];
      foreach ($code_object->getScripts() as $key => $script) {
      */
      /*
        if ($script instanceof LookListStatement)
          foreach ($script->getStatements() as $statement) {
            if ($statement instanceof LookStatement)
              $statements = $statement->getStatements();
              foreach ($statement->getStatements() as $stmt) {
                if ($stmt instanceof FileNameStatement)
                  $debug[] = 'FileNameStatement';
              }
          }
        */
        /*
        if ($script instanceof LookListStatement) {
          $debugtwo = count($script->getStatements());
          foreach ($script->getStatements() as $look_statement) {
            if ($look_statement instanceof LookStatement)
              $debug[] = $look_statement->getStatements()[0]->getValue();
          }
        */
          /*
          $stmt = $script->getStatements()[0];
          if ($stmt instanceof LookStatement) {

            $st = $stmt->getStatements()[0];
            $debug = get_class($st);
          }
          */
        /*}
      }*/

      $twig_params = array(
        'path' => $extracted_program->getWebPath(),
        // 'debug' => $debug,
        // 'debugtwo' => $debugtwo,
        'background' => $background,
        'object_list' => $object_list
      );
    }

    return $twig_params;
  }

  private function formatObject($code_object) {
    $looks = array();
    $scripts = array();
    $sounds = array();

    foreach ($code_object->getScripts() as $statement) {
      if ($statement instanceof LookListStatement) {
        $looks = $this->formatLooks($statement);
      } else if ($statement instanceof SoundListStatement) {
        $sounds = $this->formatSounds($statement);
      } else if ($statement instanceof ScriptListStatement) {
        $scripts = $this->formatScripts($statement);
      }
    }

    return array(
      'name' => $code_object->getName(),
      'looks' => $looks,
      'scripts' => $scripts,
      'sounds' => $sounds
    );
  }

  private function formatLooks($look_list_statement) {
    $looks = array();
    foreach ($look_list_statement->getStatements() as $look_statement) {
      $looks[] = array(
        'look_name' => $look_statement->getValue(),
        'look_url' => $look_statement->getStatements()[0]->getValue()
      );
    }
    return $looks;
  }

  private function formatSounds($sound_list_statement) {
    $sounds = array();
    foreach ($sound_list_statement->getStatements() as $sound_statement) {
      $sounds[] = array(
        'sound_name' => $sound_statement->getName(),
        'sound_url' => $sound_statement->getStatements()[0]->getValue()
      );
    }
    return $sounds;
  }

  private function formatScripts($script_list_statement) {
    $scripts = array();
    $scripts = $script_list_statement->getStatements();
    return $scripts;
  }
}