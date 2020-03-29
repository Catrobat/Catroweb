<?php

namespace App\Catrobat\Requests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class AppRequest
{
  protected RequestStack $request_stack;

  public function __construct(RequestStack $request_stack)
  {
    $this->request_stack = $request_stack;
  }

  public function getRequestStack(): RequestStack
  {
    return $this->request_stack;
  }

  public function getCurrentRequest(): ?Request
  {
    return $this->request_stack->getCurrentRequest();
  }

  /**
   * Checks if "BuildType/debug" is defined in the User-Agent string.
   */
  public function isDebugBuildRequest(): bool
  {
    $request = $this->request_stack->getCurrentRequest();
    if (null === $request)
    {
      return false;
    }

    $user_agent = $request->headers->get('User-Agent');
    if (null === $user_agent)
    {
      return false;
    }

    return false !== stripos($user_agent, ' buildtype/debug');
  }

  /**
   * Checks if "theme/*" is defined in the User-Agent string.
   */
  public function getThemeDefinedInRequest(): string
  {
    $request = $this->request_stack->getCurrentRequest();
    if (null === $request)
    {
      return '';
    }

    $user_agent = $request->headers->get('User-Agent');
    if (null === $user_agent)
    {
      return '';
    }

    $user_agent_attributes = explode(' ', strtolower($user_agent));

    foreach ($user_agent_attributes as $attribute)
    {
      if (false !== strpos($attribute, 'theme/'))
      {
        return str_replace('theme/', '', $attribute);
      }
    }

    return '';
  }
}
