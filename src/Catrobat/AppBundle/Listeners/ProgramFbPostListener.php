<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramRepository;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\StatusCode;
use Facebook\FacebookSDKException;
use Symfony\Component\Routing\Router;
use Symfony\Component\DependencyInjection\Container;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;

//DO NOT REMOVE (CODE FOR v5.0.0):
/*use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookRequest;*/

class ProgramFbPostListener
{
    private $router;
    private $container;

    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function onProgramAfterInsert(ProgramAfterInsertEvent $event)
    {

       echo  $this->container->getParameter('fb_share_app_id');

        $this->postOnFacebook($event->getProgramEntity());
    }

    public function postOnFacebook(Program $program)
    {
        //DO NOT REMOVE (CODE FOR v5.0.0):
        /*$fb = new Facebook([
            'app_id' => '1457008381269887',
            'app_secret' => '075d689732f585c36a7a987cfc78a408',
            'default_graph_version' => 'v2.4',
        ]);*/

        $app_id = $this->container->getParameter('fb_share_app_id');
        $app_secret = $this->container->getParameter('fb_share_app_secret');
        $access_token = $this->container->getParameter('fb_share_access_token');

//        FacebookSession::setDefaultApplication('1457008381269887', '075d689732f585c36a7a987cfc78a408');
        FacebookSession::setDefaultApplication($app_id, $app_secret);

//        $accessToken = "CAAUtJDMAF38BAP09AD1u8WyDGoQQtfhSTZAnXl5XgUViy0YDaDyPjsCsDdTvqa8Dgr1y5JL9aiQZBz1Rm8jbsDihNU2AvUtQ3Hacm7jxsYNqirNmNPbs64ATyvfQOunPcZBAsFV5ORebQOl1kNbKmapWBthdN4upBLgZA9hl93DV8aLlAvUZB";

        $session = new FacebookSession($access_token);

        if (isset($session)) {
            $url = "https://share.catrob.at" . $this->router->generate('program', array('id' => $program->getId()));

            $data = [
                'link' => $url,
                'message' => $program->getName(),
            ];

            $request = new FacebookRequest(
                $session,
                'POST',
                '/me/feed',
                $data
                );

            $response = $request->execute()->getGraphObject();
            $program->setFbPostId($response->getProperty("id"));

            //DO NOT REMOVE (CODE FOR v5.0.0):

            /*$client = $fb->getOAuth2Client();

            try {
                $accessToken = $client->getLongLivedAccessToken($accessToken);
            } catch(FacebookSDKException $e) {
                return $e->getMessage();
            }

            if (isset($accessToken)) {
                $url = "https://share.catrob.at" . $this->router->generate('program', array('id' => $program->getId()));

                $data = [
                    'link' => $url,
                    'message' => $program->getName(),
                ];

                $response = $fb->post('/me/feed', $data, (string) $accessToken);
                $respBody = json_decode($response->getBody());

                $program->setFbPostId($respBody->id);

                echo $respBody->id;
            }*/
        }
        else {
            throw new FacebookSDKException("Invalid facebook session.", StatusCode::FB_POST_ERROR);
        }
    }
}
