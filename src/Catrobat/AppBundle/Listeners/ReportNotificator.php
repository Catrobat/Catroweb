<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ReportInsertEvent;

use Catrobat\AppBundle\StatusCode;
use Facebook\FacebookSDKException;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;


//DO NOT REMOVE (CODE FOR v5.0.0):
/*use Catrobat\AppBundle\StatusCode;
use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;*/

class ReportNotificator
{
    private $mailer;
    private $notification_repo;

    public function __construct(\Swift_Mailer $mailer,  \Catrobat\AppBundle\Entity\NotificationRepository $repository)
    {
        $this->mailer = $mailer;
        $this->notification_repo = $repository;
    }

    public function onReportInsertEvent(ReportInsertEvent $event)
    {
    /* @var $notification_repo \Catrobat\AppBundle\Entity\NotificationRepository */

        $notification_repo = $this->notification_repo;
        $all_users = $notification_repo->findAll();
        $notification = $event->getReport();
        $program = $notification->getProgram();

        $this->removeFbPost($program);

        foreach ($all_users as $user) {
            /* @var $user \Catrobat\AppBundle\Entity\Notification */
      if (!$user->getReport()) {
          continue;
      }

            $message = \Swift_Message::newInstance()
      ->setSubject('[Pocketcode] reported project!')
      ->setFrom('noreply@catrob.at')
      ->setTo($user->getUser()->getEmail())
      ->setContentType('text/html')
       ->setBody('A Project got reported!

Note: '.$event->getNote().'
Project Name:'.$program->getName().'
Project Description: '.$program->getDescription().'

');

            $this->mailer->send($message);
        }
    }

    public function removeFbPost($program)
    {
         if($program->getFbPostId() != null) {

             //DO NOT REMOVE (CODE FOR v5.0.0):

             /*$fb = new Facebook([
                 'app_id' => '1457008381269887',
                 'app_secret' => '075d689732f585c36a7a987cfc78a408',
                 'default_graph_version' => 'v2.4',
             ]);*/

             FacebookSession::setDefaultApplication('1457008381269887', '075d689732f585c36a7a987cfc78a408');

             $accessToken = "CAAUtJDMAF38BAP09AD1u8WyDGoQQtfhSTZAnXl5XgUViy0YDaDyPjsCsDdTvqa8Dgr1y5JL9aiQZBz1Rm8jbsDihNU2AvUtQ3Hacm7jxsYNqirNmNPbs64ATyvfQOunPcZBAsFV5ORebQOl1kNbKmapWBthdN4upBLgZA9hl93DV8aLlAvUZB";
             $session = new FacebookSession($accessToken);

             if (isset($session)) {
                 $request = new FacebookRequest(
                     $session,
                     'DELETE',
                     '/' . $program->getFbPostId()
                 );

                 try {
                     $response = $request->execute();
                 }
                 catch(FacebookSDKException $e) {
                     echo $e->getMessage();
                 }

                 $program->setFbPostId(null);

                 //DO NOT REMOVE (CODE FOR v5.0.0):

                 /*$client = $fb->getOAuth2Client();

                 try {
                     $accessToken = $client->getLongLivedAccessToken($accessToken);
                 } catch (FacebookSDKException $e) {
                     return $e->getMessage();
                 }

                 if (isset($accessToken)) {

                     $data = [
                         'post_id' => "/1489840667992950",
                     ];

                     try {
                         $response = $fb->delete('1481344872175863_1489840667992950', $data, (string)$accessToken);
                         return $response->getHttpStatusCode();
                     } catch (FacebookSDKException $e) {
                         return StatusCode::FB_DELETE_ERROR;
                     }
                 }*/
             }
             else {
                 throw new FacebookSDKException("Invalid facebook session", StatusCode::FB_DELETE_ERROR);
             }
         }

    }
}
