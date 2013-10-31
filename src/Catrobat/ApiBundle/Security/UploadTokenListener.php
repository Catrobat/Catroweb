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

class UploadTokenListener implements ListenerInterface
{
  protected $securityContext;
  protected $authenticationManager;
  protected $templating;
  protected $provider;

  public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $providerkey, EngineInterface $templating)
  {
    $this->securityContext = $securityContext;
    $this->authenticationManager = $authenticationManager;
    $this->templating = $templating;
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
    
    $content = $this->templating->render('CatrobatApiBundle:Api:TokenAuthenticationFailed.json.twig');
    $response = new Response($content);
    $response->setStatusCode(403);
    $event->setResponse($response);
  }

  protected function newTokenMissingResponse()
  {
    $content = $this->templating->render('CatrobatApiBundle:Api:TokenAuthenticationFailed.json.twig');
    $response = new Response($content);
    $response->setStatusCode(401);
    return $response;
  }

}
