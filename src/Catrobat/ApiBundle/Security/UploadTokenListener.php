<?php

namespace Catrobat\ApiBundle\Security;

use Catrobat\CoreBundle\StatusCode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\Translator;

class UploadTokenListener implements ListenerInterface
{
  protected $securityContext;
  protected $authenticationManager;
  protected $provider;
  protected $translator;

  public function __construct(SecurityContextInterface $securityContext, AuthenticationManagerInterface $authenticationManager, $providerkey, Translator $translator)
  {
    $this->securityContext = $securityContext;
    $this->authenticationManager = $authenticationManager;
    $this->provider = $providerkey;
    $this->translator = $translator;
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
    
    $response = JsonResponse::create(array("statusCode" => StatusCode::LOGIN_ERROR, "answer" => $this->trans("error.token"), "preHeaderMessages" => ""),Response::HTTP_FORBIDDEN);
    $event->setResponse($response);
  }

  protected function newTokenMissingResponse()
  {
    $response = JsonResponse::create(array("statusCode" => StatusCode::LOGIN_ERROR, "answer" => $this->trans("error.token"), "preHeaderMessages" => ""),Response::HTTP_UNAUTHORIZED);
    return $response;
  }

  private function trans($message, $parameters = array())
  {
    return $this->translator->trans($message,$parameters,"catroweb_api");
  }

}
