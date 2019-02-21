<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\StatusCode;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\FacebookResponse;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

/**
 * Class FacebookPostService
 * @package Catrobat\AppBundle\Services
 */
class FacebookPostService
{
  /**
   * @var Router
   */
  private $router;
  /**
   * @var Container
   */
  private $container;
  /**
   * @var ScreenshotRepository
   */
  private $screenshot_repository;
  /**
   * @var
   */
  private $facebook;
  /**
   * @var
   */
  private $app_id;
  /**
   * @var
   */
  private $app_secret;
  /**
   * @var
   */
  private $fb_admin_id;
  /**
   * @var
   */
  private $fb_channel_id;
  /**
   * @var
   */
  private $fb_admin_user_token;
  /**
   * @var
   */
  private $debug;

  /**
   * FacebookPostService constructor.
   *
   * @param Router               $router
   * @param Container            $container
   * @param ScreenshotRepository $screenshot_repository
   */
  public function __construct(Router $router, Container $container, ScreenshotRepository $screenshot_repository)
  {
    $this->router = $router;
    $this->container = $container;
    $this->screenshot_repository = $screenshot_repository;
  }

  /**
   * @param $post_id
   *
   * @return int
   * @throws FacebookSDKException
   */
  public function removeFbPost($post_id)
  {
    if ($this->debug)
    {
      echo 'Post ID:' . $post_id;
    }

    if ($post_id != null)
    {

      try
      {
        $fb_response = $this->checkFacebookPostAvailable($post_id);
      } catch (FacebookSDKException $e)
      {
        return StatusCode::FB_DELETE_ERROR;
      }

      $response_string = print_r($fb_response, true);

      if (strpos($response_string, 'id') == false)
      {
        if ($this->debug)
        {
          echo 'Post not existing!';
        }

        return StatusCode::FB_DELETE_ERROR;
      }

      $account_access_token = $this->checkFacebookServerAccessTokenValidity();
      $this->setFacebookDefaultAccessToken($this->getAppToken());

      $is_valid = $this->debugToken($account_access_token);
      if ($this->debug)
      {
        echo 'valid:' . $is_valid;
      }

      if ($is_valid)
      {

        $client = $this->facebook->getOAuth2Client();
        try
        {
          //refresh access token with long-term admin user-token
          $accessToken = $client->getLongLivedAccessToken($account_access_token);
        } catch (FacebookSDKException $e)
        {
          return $e->getMessage();
        }

        if (isset($accessToken))
        {

          try
          {
            $response = $this->facebook->delete($post_id, [], (string)$accessToken);

            if ($this->debug)
            {
              echo 'body:' . $response->getBody();
              echo 'response code:' . $response->getHttpStatusCode();
            }

            return $response->getHttpStatusCode();
          } catch (FacebookSDKException $e)
          {
            return StatusCode::FB_DELETE_ERROR;
          }
        }
      }
      else
      {
        throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_DELETE_ERROR);
      }
    }
  }

  /**
   * @param $post_id
   *
   * @return int|string
   * @throws FacebookSDKException
   */
  public function getFacebookPostUrl($post_id)
  {
    if ($this->facebook == null)
    {
      $this->initializeFacebook();
    }

    $account_access_token = $this->checkFacebookServerAccessTokenValidity();
    $this->setFacebookDefaultAccessToken($this->getAppToken());
    $is_valid = $this->debugToken($account_access_token);
    if ($this->debug)
    {
      echo 'valid:' . $is_valid;
    }

    if ($is_valid)
    {

      $client = $this->facebook->getOAuth2Client();
      try
      {
        //refresh access token with long-term admin user-token
        $accessToken = $client->getLongLivedAccessToken($account_access_token);
      } catch (FacebookSDKException $e)
      {
        return $e->getMessage();
      }

      if (isset($accessToken))
      {

        try
        {
          /**
           * @var $response FacebookResponse
           */
          $response = $this->facebook->get($post_id . '?fields=id,link,name,actions', (string)$accessToken);
          if ($this->debug)
          {
            echo 'HEADERS: ' . print_r($response->getHeaders());
            echo 'BODY: ' . $response->getBody();
          }

          $respBody = json_decode($response->getBody());
          $post_url = $respBody->actions[0]->link;

          return $post_url;
        } catch (FacebookSDKException $e)
        {
          return StatusCode::FB_DELETE_ERROR;
        }
      }
    }
    else
    {
      throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_DELETE_ERROR);
    }
  }

  /**
   * @param $post_id
   *
   * @return int|string
   * @throws FacebookSDKException
   */
  public function checkFacebookPostAvailable($post_id)
  {
    $this->initializeFacebook();
    $account_access_token = $this->checkFacebookServerAccessTokenValidity();
    $this->setFacebookDefaultAccessToken($this->getAppToken());

    $is_valid = $this->debugToken($account_access_token);
    if ($this->debug)
    {
      echo 'valid:' . $is_valid;
    }

    if ($is_valid)
    {

      $client = $this->facebook->getOAuth2Client();
      try
      {
        //refresh access token with long-term admin user-token
        $accessToken = $client->getLongLivedAccessToken($account_access_token);
      } catch (FacebookSDKException $e)
      {
        return $e->getMessage();
      }

      if (isset($accessToken))
      {

        try
        {
          $response = $this->facebook->get($post_id, (string)$accessToken);
          if ($this->debug)
          {
            echo 'HEADERS: ' . print_r($response->getHeaders());
            echo 'BODY: ' . $response->getBody();
          }

          return $response;
        } catch (FacebookSDKException $e)
        {
          return StatusCode::FB_DELETE_ERROR;
        }
      }
    }
    else
    {
      throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_DELETE_ERROR);
    }
  }

  /**
   * @param $program_id
   *
   * @return mixed
   * @throws FacebookSDKException
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function postOnFacebook($program_id)
  {
    $this->initializeFacebook();
    $account_access_token = $this->checkFacebookServerAccessTokenValidity();
    $this->setFacebookDefaultAccessToken($this->getAppToken());

    $is_valid = $this->debugToken($account_access_token);

    if ($this->debug)
    {
      echo 'valid:' . $is_valid;
    }

    if ($is_valid)
    {

      $client = $this->facebook->getOAuth2Client();

      try
      {
        //refresh access token with long-term admin user-token
        $accessToken = $client->getLongLivedAccessToken($account_access_token);
      } catch (FacebookSDKException $e)
      {
        return $e->getMessage();
      }

      /**
       * @var $program_manager ProgramManager
       * @var $program         Program
       * @var $response        FacebookResponse
       */
      $program_manager = $this->container->get('programmanager');
      $program = $program_manager->find($program_id);

      $url = $this->router->generate('program', ['id' => $program_id], true);
      $user = $program->getUser();
      $profile_url = $this->router->generate('profile', ['id' => $user->getId()], true);

      $message = $program->getName() . chr(10) . 'by ' . $profile_url;

      $program_img = $this->screenshot_repository->getScreenshotWebPath($program->getId());
      $program_img_url = $this->router->getContext()->getHost() . '/' . $program_img;

      $data = [
        'link'    => $url,
        'message' => $message,
        'picture' => $program_img_url,
      ];

      $response = $this->facebook->post('/me/feed', $data, (string)$accessToken);
      $respBody = json_decode($response->getBody());
      if ($this->debug)
      {
        echo $response->getBody();
      }

      $program->setFbPostId($respBody->id);
      $fb_post_id = $respBody->id;

      $fb_post_url = $this->getFacebookPostUrl($fb_post_id);
      $program->setFbPostUrl($fb_post_url);

      $entity_manager = $this->container->get('doctrine.orm.entity_manager');
      $entity_manager->persist($program);
      $entity_manager->flush();

      return $fb_post_id;
    }
    else
    {
      throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_POST_ERROR);
    }
  }

  /**
   * @return mixed
   */
  private function checkFacebookServerAccessTokenValidity()
  {
    $is_valid = $this->debugToken($this->fb_admin_user_token);

    if ($is_valid)
    {
      try
      {
        $accountsEdge = $this->facebook->get('/' . $this->fb_admin_id . '/accounts', $this->fb_admin_user_token)->getGraphEdge();
        do
        {
          foreach ($accountsEdge as $page)
          {

            $id = $page['id'];

            if ($id == $this->fb_channel_id)
            {
              $account_access_token = $page['access_token'];

              if ($this->debug)
              {
                $name = $page['name'];
                $category = $page['category'];
                $perms = $page['perms'];
                echo 'token:' . $account_access_token;
                echo 'category:' . $category;
                echo 'name:' . $name;
                echo 'id:' . $id;
                echo 'perms:' . $perms;
              }

              return $account_access_token;
            }
          }
        } while ($accountsEdge = $this->facebook->next($accountsEdge));
      } catch (FacebookResponseException $e)
      {
        if ($this->debug)
        {
          echo 'Graph returned an error: ' . $e->getMessage();
        }
      } catch (FacebookSDKException $e)
      {
        if ($this->debug)
        {
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
        }
      }
    }
  }

  /**
   * @param $token_to_check
   *
   * @return bool
   */
  private function debugToken($token_to_check)
  {
    try
    {
      $response = $this->facebook->get('/debug_token?input_token=' . $token_to_check, $this->fb_admin_user_token);
      $token_graph_node = $response->getGraphNode();
    } catch (FacebookResponseException $e)
    {
      if ($this->debug)
      {
        echo 'Graph API returned an error during token exchange for GET' . $e;
      }

      return false;
    } catch (FacebookSDKException $e)
    {
      if ($this->debug)
      {
        echo 'Error during token exchange for GET with exception' . $e;
      }

      return false;
    }

    $is_valid = $token_graph_node->getField('is_valid');

    if ($this->debug)
    {
      $app_id_debug = $token_graph_node->getField('app_id');
      $application_name_debug = $token_graph_node->getField('application');
      $facebookId_debug = $token_graph_node->getField('user_id');
      $expires = $token_graph_node->getField('expires_at');

      $scopes = $token_graph_node->getField('scopes');

      echo ' *App-ID:' . $app_id_debug;
      echo ' *App-Name:' . $application_name_debug;
      echo ' *Facebook-ID:' . $facebookId_debug;
      echo ' *is_valid:' . $is_valid;
      $result = $expires->format('Y-m-d H:i:s');
      if ($result)
      {
        echo ' *expires:' . $result;
      }
      echo ' *scopes:';
      print_r($scopes);
    }

    return $is_valid;
  }

  /**
   * @return string
   */
  private function getAppToken()
  {
    $app_token = $this->app_id . '|' . $this->app_secret;

    return $app_token;
  }

  /**
   * @param null $client_token
   */
  private function setFacebookDefaultAccessToken($client_token = null)
  {
    if ($client_token)
    {
      $this->facebook->setDefaultAccessToken($client_token);
    }
  }

  /**
   * @throws FacebookSDKException
   */
  private function initializeFacebook()
  {
    $this->setFacebookChannelConfigurationData();
    if (!$this->app_secret || !$this->app_id)
    {
      if ($this->debug)
      {
        echo "Facebook app authentication data not found!";
      }
    }
    else
    {
      $this->facebook = new Facebook([
        'app_id'                => $this->app_id,
        'app_secret'            => $this->app_secret,
        'default_graph_version' => 'v2.5',
      ]);
    }
  }

  /**
   *
   */
  private function setFacebookChannelConfigurationData()
  {
    $this->app_id = $this->container->getParameter('facebook_share_app_id');
    $this->app_secret = $this->container->getParameter('facebook_share_app_secret');
    $this->fb_channel_id = $this->container->getParameter('facebook_share_channel_id');
    $this->fb_admin_user_token = $this->container->getParameter('facebook_share_access_token');
    $this->fb_admin_id = $this->container->getParameter('facebook_share_admin_fb_id');
    $this->debug = false;
    if ($this->debug)
    {
      echo 'App-ID: ' . $this->app_id;
      echo 'App-Secret: ' . $this->app_secret;
      echo 'Channel-ID: ' . $this->fb_channel_id;
      echo 'Admin-Token: ' . $this->fb_admin_user_token;
      echo 'Admin-ID: ' . $this->fb_admin_id;
    }
  }
}
