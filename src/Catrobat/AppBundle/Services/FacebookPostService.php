<?php

namespace Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\StatusCode;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Router;

class FacebookPostService
{
    private $router;
    private $container;
    private $facebook;
    private $app_id;
    private $app_secret;
    private $fb_admin_id;
    private $fb_channel_id;
    private $fb_channel_name;
    private $fb_admin_user_token;
    private $debug;

    public function __construct(Router $router, Container $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    public function removeFbPost($post_id)
    {
        if ($this->debug) {
            echo 'ID:' . $post_id;
        }

        if ($post_id != null) {

            $fb_response = $this->checkFacebookPostAvailable($post_id);
            $response_string = print_r($fb_response, true);

            if (strpos($response_string,'id') == false) {
                if ($this->debug) {
                    echo 'Post not existing!';
                }
                return StatusCode::FB_DELETE_ERROR;
            }

            $account_access_token = $this->checkFacebookServerAccessTokenValidity();
            $this->setFacebookDefaultAccessToken($this->getAppToken());

            $is_valid = $this->debugToken($account_access_token);
            if ($this->debug) {
                echo 'valid:' . $is_valid;
            }

            if ($is_valid) {

                $client = $this->facebook->getOAuth2Client();
                try {
                    //refresh access token with long-term admin user-token
                    $accessToken = $client->getLongLivedAccessToken($account_access_token);
                } catch (FacebookSDKException $e) {
                    return $e->getMessage();
                }

                if (isset($accessToken)) {

                    try {
                        $response = $this->facebook->delete($post_id, [], (string) $accessToken);

                        if ($this->debug) {
                            echo 'body:' . $response->getBody();
                            echo 'response code:' . $response->getHttpStatusCode();
                        }

                        return $response->getHttpStatusCode();
                    } catch (FacebookSDKException $e) {
                        return StatusCode::FB_DELETE_ERROR;
                    }
                }
            } else {
                throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_DELETE_ERROR);
            }
        }
    }

    public function checkFacebookPostAvailable($post_id)
    {
        $this->initializeFacebook();
        $account_access_token = $this->checkFacebookServerAccessTokenValidity();
        $this->setFacebookDefaultAccessToken($this->getAppToken());

        $is_valid = $this->debugToken($account_access_token);
        if ($this->debug) {
            echo 'valid:' . $is_valid;
        }

        if ($is_valid) {

            $client = $this->facebook->getOAuth2Client();
            try {
                //refresh access token with long-term admin user-token
                $accessToken = $client->getLongLivedAccessToken($account_access_token);
            } catch (FacebookSDKException $e) {
                return $e->getMessage();
            }

            if (isset($accessToken)) {

                try {
                    $response = $this->facebook->get($post_id, (string) $accessToken);
                    if ($this->debug) {
                        echo 'HEADERS: ' . print_r($response->getHeaders());
                        echo 'BODY: ' . $response->getBody();
                    }
                    return $response;
                } catch (FacebookSDKException $e) {
                    return StatusCode::FB_DELETE_ERROR;
                }
            }
        } else {
            throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_DELETE_ERROR);
        }
    }

    public function postOnFacebook(Program $program)
    {
        $this->initializeFacebook();
        $account_access_token = $this->checkFacebookServerAccessTokenValidity();
        $this->setFacebookDefaultAccessToken($this->getAppToken());

        $is_valid = $this->debugToken($account_access_token);

        if ($this->debug) {
            echo 'valid:' . $is_valid;
        }

        if ($is_valid) {

            $client = $this->facebook->getOAuth2Client();

            try {
                //refresh access token with long-term admin user-token
                $accessToken = $client->getLongLivedAccessToken($account_access_token);
            } catch (FacebookSDKException $e) {
                return $e->getMessage();
            }

            $url = "https://share.catrob.at" . $this->router->generate('program', array('id' => $program->getId()));

            $data = [
                'link' => $url,
                'message' => $program->getName(),
            ];

            $response = $this->facebook->post('/me/feed', $data, (string) $accessToken);
            $respBody = json_decode($response->getBody());
            if ($this->debug) {
                echo $response->getBody();
            }

            $program->setFbPostId($respBody->id);
            echo $respBody->id;
            return $respBody->id;
        } else {
            throw new FacebookSDKException("Invalid facebook user or page token", StatusCode::FB_POST_ERROR);
        }
    }

    public function checkFacebookServerAccessTokenValidity()
    {
        $is_valid = $this->debugToken($this->fb_admin_user_token);

        if ($is_valid) {
            try {
                $accountsEdge = $this->facebook->get('/' . $this->fb_admin_id . '/accounts', $this->fb_admin_user_token)->getGraphEdge();
                do {
                    foreach ($accountsEdge as $page) {

                        $name = $page['name'];
                        $id = $page['id'];
                        $account_access_token = $page['access_token'];

                        if ($name == $this->fb_channel_name || $id == $this->fb_channel_id) {
                            if ($this->debug) {
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
            } catch (FacebookResponseException $e) {
                if ($this->debug) {
                    echo 'Graph returned an error: ' . $e->getMessage();
                }
            } catch (FacebookSDKException $e) {
                if ($this->debug) {
                    echo 'Facebook SDK returned an error: ' . $e->getMessage();
                }
            }
        }
    }

    public function debugToken($token_to_check)
    {
        try {
            $response = $this->facebook->get('/debug_token?input_token=' . $token_to_check, $this->fb_admin_user_token);
            $token_graph_node = $response->getGraphNode();
        } catch (FacebookResponseException $e) {
            if ($this->debug) {
                echo 'Graph API returned an error during token exchange for GET' . $e;
            }
            return false;
        } catch (FacebookSDKException $e) {
            if ($this->debug) {
                echo 'Error during token exchange for GET with exception' . $e;
            }
            return false;
        }

        $is_valid = $token_graph_node->getField('is_valid');

        if ($this->debug) {
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
            if ($result) {
                echo ' *expires:' . $result;
            }
            echo ' *scopes:';
            print_r($scopes);
        }
        return $is_valid;
    }

    public function getAppToken()
    {
        $app_token = $this->app_id . '|' . $this->app_secret;
        return $app_token;
    }

    public function setFacebookDefaultAccessToken($client_token = NULL)
    {
        if ($client_token) {
            $this->facebook->setDefaultAccessToken($client_token);
        }
    }

    private function initializeFacebook()
    {
        $this->setFacebookChannelConfigurationData();
        if (!$this->app_secret || !$this->app_id) {
            if ($this->debug) {
                echo "Facebook app authentication data not found!";
            }
        } else {
            $this->facebook = new Facebook([
                'app_id' => $this->app_id,
                'app_secret' => $this->app_secret,
                'default_graph_version' => 'v2.5',
            ]);
        }
    }

    private function setFacebookChannelConfigurationData()
    {
        if (in_array($this->container->get('kernel')->getEnvironment(), array('test', 'dev'))) {
            $this->app_id = $this->container->getParameter('fb_share_test_app_id');
            $this->app_secret = $this->container->getParameter('fb_share_test_app_secret');
            $this->fb_channel_id = '1469491976688194';
            $this->fb_channel_name = 'Catrobat Programs Test';
            $this->fb_admin_user_token = $this->container->getParameter('fb_share_test_access_token');
            $this->debug = false;
        } else {
            $this->app_id = $this->container->getParameter('fb_share_app_id');
            $this->app_secret = $this->container->getParameter('fb_share_app_secret');
            $this->fb_channel_id = '1481344872175863';
            $this->fb_channel_name = 'Catrobat Programs';
            $this->fb_admin_user_token = $this->container->getParameter('fb_share_access_token');
            $this->debug = false;
        }
        $this->fb_admin_id = $this->container->getParameter('fb_share_admin_fb_id');

    }
}
