<?php

namespace Catrobat\ApiBundle\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadTokenListener implements ListenerInterface
{
  protected $securityContext;
  protected $authenticationManager;
  protected $provider;

  public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $providerkey)
  {
    $this->securityContext = $securityContext;
    $this->authenticationManager = $authenticationManager;
    $this->provider = $providerkey;
  }

  public function handle(GetResponseEvent $event)
  {
    $request = $event->getRequest();
    
    $upload_token = $request->request->get('token');
    $username = $request->request->get('username');
    
    if ($upload_token == "")
    {
      $event->setResponse($this->newTokenMissingResponse());
      return;
    }
    
    $token = new PreAuthenticatedToken($username, $upload_token, $this->provider);
    
    try
    {
      $authToken = $this->authenticationManager->authenticate($token);
      $this->securityContext->setToken($authToken);
      return;
    }
    catch (AuthenticationException $failed)
    {
    }
    
    $response = JsonResponse::create(array("statusCode" => 601, "answer" => "Authentication of device failed: invalid auth-token!", "preHeaderMessages" => ""),403);
    $event->setResponse($response);
  }

  protected function newTokenMissingResponse()
  {
    $response = JsonResponse::create(array("statusCode" => 601, "answer" => "Authentication of device failed: invalid auth-token!", "preHeaderMessages" => ""),401);
    return $response;
  }

}
