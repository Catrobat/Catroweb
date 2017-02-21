<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportController extends Controller
{
  public function unreportAction(Request $request = null)
  {

    /* @var $object \Catrobat\AppBundle\Entity\UserComment */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }

    $object->setIsReported(false);
    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', 'Report ' . $object->getId() . ' removed from list');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function deleteCommentAction(Request $request = null)
  {
    /* @var $object \Catrobat\AppBundle\Entity\UserComment */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($object->getId());

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $object->getId());
    }
    $em->remove($comment);
    $em->flush();
    $this->addFlash('sonata_flash_success', 'Comment ' . $object->getId() . ' deleted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function warnAction(Request $request = null)
  {
    /* @var $object \Catrobat\AppBundle\Entity\UserComment */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($object->getId());

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $object->getId());
    }

    $user = $em->getRepository('AppBundle:User')->find($comment->getUserId());

    if (!$user)
    {
      throw $this->createNotFoundException(
        'No user found for this id ' . $comment->getUserId());
    }
    $user->setWarned(true);
    $admin_message = 'User ' . $user->getUsername() . ' successfully warned.';
    $mail_message = 'Hi ' . $user->getUsername() . ".\n";
    $mail_message .= 'This is a warning. It seems like you have violated our terms of service. 
    If you violate our terms of service again your PocketCode account will be banned.' .
      'The reason for warning ban is:';
    $reason = "Violation of our Terms of service. You can find them here: https://share.catrob.at/pocketcode/termsOfUse";
    $reason .= "\n\nText of the comment you have been warned for: \n";
    $reason .= $comment->getText();
    $mail_message .= "\n" . $reason;

    $em->remove($comment);
    $em->flush();

    $mail_address = $user->getEmail();

    $mail_content = wordwrap($mail_message, 70);
    $headers = "From: webmaster@catrob.at" . "\r\n";
    mail($mail_address, "Your PocketCode account has been banned", $mail_content, $headers);

    $this->addFlash('sonata_flash_success', $admin_message);

//        $this->addFlash('sonata_flash_success', 'Message is: ' . $mail_message);
    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function bannAction(Request $request = null)
  {
    /* @var $object \Catrobat\AppBundle\Entity\UserComment */
    $object = $this->admin->getSubject();

    if (!$object)
    {
      throw new NotFoundHttpException();
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($object->getId());

    if (!$comment)
    {
      throw $this->createNotFoundException(
        'No comment found for this id ' . $object->getId());
    }

    $user = $em->getRepository('AppBundle:User')->find($comment->getUserId());

    if (!$user)
    {
      throw $this->createNotFoundException(
        'No user found for this id ' . $comment->getUserId());
    }

    $ban_duration = $user->ban();
    $admin_message = 'User ' . $user->getUsername() . ' successfully banned for ';
    $mail_message = 'Hi ' . $user->getUsername() . ".\n";

    switch ($ban_duration)
    {
      case 1:
        $admin_message .= "1 day.";
        $mail_message .= 'We are sorry to tell you that your PocketCode account has been banned for 24 hours ' .
          'because you violated our terms of service. If you violate our terms of service again your next ' .
          'ban will last a whole week. The third ban will be permanent. The reason for your ban is:';
        break;
      case 7:
        $admin_message .= "1 week.";
        $mail_message .= 'We are sorry to tell you that your PocketCode account has been banned for 1 week ' .
          'because you violated our terms of service. If you violate our terms of service again your next ' .
          'ban will be permanent. The reason for your ban is:';
        break;
      case 99:
        $admin_message = 'User ' . $user->getUsername() . ' was permanently banned.';
        $mail_message .= 'We are sorry to tell you that your PocketCode account has been permanently banned. ' .
          'The reason for your ban is:';
        break;
      default:
        $admin_message = 'User ' . $user->getUsername() . ' was permanently banned.';
        $mail_message .= 'We are sorry to tell you that your PocketCode account has been permanently banned. ' .
          'The reason for your ban is:';
        break;
    }

//        $reason = $request->query->get('reason');
    $reason = "Violation of our Terms of service. You can find them here: https://share.catrob.at/pocketcode/termsOfUse";
    $reason .= "\nText of the comment you have been banned for: \n";
    $reason .= $comment->getText();
    $mail_message .= "\n" . $reason;

    $em->remove($comment);
    $em->flush();

    $mail_address = $user->getEmail();

    $mail_content = wordwrap($mail_message, 70);
    $headers = "From: webmaster@catrob.at" . "\r\n";
    mail($mail_address, "Your PocketCode account has been banned", $mail_content, $headers);

    $this->addFlash('sonata_flash_success', $admin_message);

//        $this->addFlash('sonata_flash_success', 'Message is: ' . $mail_message);
    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}