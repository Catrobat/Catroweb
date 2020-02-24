<?php

namespace App\Catrobat\Commands\Helpers;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\FeaturedProgram;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramInappropriateReport;
use App\Entity\ProgramLike;
use App\Entity\User;
use App\Entity\UserComment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class ResetController
 * @package App\Catrobat\Commands\Helpers
 */
class ResetController extends AbstractController
{
  /**
   * @param Program $program
   */
  public function featureProgram(Program $program)
  {
    $entity_manager = $this->getDoctrine()->getManager();
    $feature = new FeaturedProgram();
    $feature->setProgram($program);
    $feature->setActive(true);
    $feature->setFlavor('pocketcode');
    $feature->setImageType('jpeg'); //todo picture?
    $feature->setUrl(null);

    $source_img = "public/resources/screenshots/screen_" . $program->getId() . ".png";
    $dest_img = "public/resources/featured/screen_" . $program->getId() . ".png";
    copy($source_img, $dest_img);
    $file = new File($dest_img);
    $feature->setNewFeaturedImage($file);

    $entity_manager->persist($feature);
    $entity_manager->flush();
  }

  /**
   * @param Program $program
   * @param User    $user
   */
  public function likeProgram(Program $program, User $user)
  {
    $entity_manager = $this->getDoctrine()->getManager();
    $like = new ProgramLike($program, $user, array_rand(ProgramLike::$TYPE_NAMES));
    $like->setCreatedAt(date_create());

    $entity_manager->persist($like);
    $entity_manager->flush();
  }

  /**
   * @param User    $user
   * @param Program $program
   * @param string  $message
   * @param bool    $reported
   */
  public function postComment(User $user, Program $program, string $message, bool $reported)
  {
    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUsername());
    $temp_comment->setUser($user);
    $temp_comment->setText($message);
    $temp_comment->setProgram($program);
    $temp_comment->setUploadDate(date_create());
    $temp_comment->setIsReported($reported);

    $em = $this->getDoctrine()->getManager();
    $em->persist($temp_comment);
    $em->flush();
    $em->refresh($temp_comment);
  }

  /**
   * @param Program $program
   * @param User    $user
   * @param string  $note
   */
  public function reportProgram(Program $program, User $user, string $note)
  {
    $entity_manager = $this->getDoctrine()->getManager();
    $report = new ProgramInappropriateReport();
    $report->setReportingUser($user);
    $program->setVisible(false);
    $report->setCategory('Inappropriate');
    $report->setNote($note);
    $report->setProgram($program);

    $entity_manager->persist($report);
    $entity_manager->flush();
  }

  /**
   * @param Program $program
   * @param User    $user
   */
  public function downloadProgram(Program $program, User $user)
  {
    $entity_manager = $this->getDoctrine()->getManager();
    $download = new ProgramDownloads();
    $download->setUser($user);
    $download->setProgram($program);
    $download->setDownloadedAt(date_create());
    $download->setIp('127.0.0.1');
    $download->setUserAgent('TestBrowser/5.0');
    $download->setLocale('de_at');
    $program->setDownloads($program->getDownloads() + 1);

    $entity_manager->persist($program);
    $entity_manager->persist($download);
    $entity_manager->flush();
  }

  /**
   * @param User $user
   * @param User $follower
   */
  public function followUser(User $user, User $follower)
  {
    $entity_manager = $this->getDoctrine()->getManager();
    $user->addFollower($follower);
    $follower->addFollowing($user);

    $entity_manager->persist($user);
    $entity_manager->persist($follower);
    $entity_manager->flush();
  }
}
