<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\User;
use App\Entity\UserManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class EmailUserMessageController
 * @package App\Catrobat\Controller\Admin
 */
class EmailUserMessageController extends CRUDController
{

  /**
   * @param Request|null $request
   *
   * @return Response
   */
  public function listAction(Request $request = null)
  {
    return $this->renderWithExtraParams('Admin/mail.html.twig');
  }

  /**
   * @param \Swift_Mailer $mailer
   * @param Request       $request
   *
   * @return Response
   */
  public function sendAction(\Swift_Mailer $mailer, Request $request)
  {
    /**
     * @var $userManager UserManager
     * @var $user        User
     */
    $userManager = $this->get('usermanager');
    $user = $userManager->findUserByUsername($request->get('username'));
    if (!$user)
    {
      return new Response("User does not exist");
    }
    $subject = $request->get('subject');
    if (!$subject || $subject === "")
    {
      return new Response("Empty subject!");
    }
    $messageText = $request->get('message');
    if (!$messageText || $messageText === "")
    {
      return new Response("Empty message!");
    }
    $htmlText = str_replace(PHP_EOL, "<br>", $messageText);
    $message = (new \Swift_Message($subject))
      ->setFrom('webteam@catrob.at')
      ->setTo($user->getEmail())
      ->setBody(
        $this->renderView(
          'Email/simple_message.html.twig',
          ['message' => $htmlText]
        ),
        'text/html'
      )
      // plaintext version of the message
      ->addPart(strip_tags($messageText), 'text/plain'
      );
    $mailer->send($message);

    return new Response("OK - message sent");
  }
}