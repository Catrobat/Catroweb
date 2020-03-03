<?php

namespace App\Catrobat\Requests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AppRequest.
 */
class AppRequest
{
  /**
   * @var RequestStack
   */
  protected $request_stack;

  /**
   * AppRequest constructor.
   */
  public function __construct(RequestStack $request_stack)
  {
    $this->request_stack = $request_stack;
  }

  public function getRequestStack(): RequestStack
  {
    return $this->request_stack;
  }

  /**
   * @return Request|null
   */
  public function getCurrentRequest()
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
    if (is_array($user_agent))
    {
      if (count($user_agent) > 0)
      {
        $user_agent = $user_agent[0];
      }
      else
      {
        return false;
      }
    }

    return false !== strpos(strtolower($user_agent), ' buildtype/debug');
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
    if (is_array($user_agent))
    {
      if (count($user_agent) > 0)
      {
        $user_agent = $user_agent[0];
      }
      else
      {
        return '';
      }
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
