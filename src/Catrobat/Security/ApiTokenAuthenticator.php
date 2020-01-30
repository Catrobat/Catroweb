<?php

namespace App\Catrobat\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;


/**
 * Class ApiTokenAuthenticator
 * @package App\Catrobat\Security
 */
class ApiTokenAuthenticator extends AbstractGuardAuthenticator
{

  /**
   * @required request parameter TOKEN
   *
   *  Must be sent in the request HEADER containing the user token
   *  Must not be empty
   *
   */
  const TOKEN = 'token';

  /**
   * @required request parameter USERNAME
   *
   *  For GET  requests must be sent in the request BODY containing
   *  For POST requests must be sent in the request as QUERY parameter
   *  Must not be empty
   *
   */
  const USERNAME = "username";


  /**
   * @var TranslatorInterface
   */
  protected $translator;

  /**
   * @var EntityManagerInterface
   */
  private $em;


  /**
   * ApiTokenAuthenticator constructor.
   *
   * @param EntityManagerInterface $em
   * @param TranslatorInterface    $translator
   */
  public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
  {
    $this->em = $em;
    $this->translator = $translator;
  }

  /**
   * Called on every request to decide if this authenticator should be
   * used for the request. Returning false will cause this authenticator
   * to be skipped.
   *
   * @param Request $request
   *
   * @return bool
   */
  public function supports(Request $request)
  {
    return $this->requestHasValidAuthTokenInHeader($request) && (
        $this->postRequestHasValidCredentialsInRequestBody($request) ||
        $this->getRequestHasValidCredentialsInQueryParameters($request)
      );
  }

  /**
   * Called on every request. Return whatever credentials you want to
   * be passed to getUser() as $credentials.
   *
   * @param Request $request
   *
   * @return array|mixed
   */
  public function getCredentials(Request $request)
  {
    $username = null;

    if ($request->isMethod(Request::METHOD_GET))
    {
      $username = $request->query->get(self::USERNAME);
    }
    else
    {
      if ($request->isMethod(Request::METHOD_POST))
      {
        $username = $request->request->get(self::USERNAME);
      }
    }

    return [
      self::TOKEN    => $request->headers->get(self::TOKEN),
      self::USERNAME => $username,
    ];
  }

  /**
   * @param mixed                 $credentials
   * @param UserProviderInterface $userProvider
   *
   * @return User|null
   */
  public function getUser($credentials, UserProviderInterface $userProvider)
  {
    $token = $credentials[self::TOKEN];
    $username = $credentials[self::USERNAME];

    if (null === $token || null === $username)
    {
      return null;
    }

    // if a User object, checkCredentials() is called
    return $this->em->getRepository(User::class)
      ->findOneBy(['upload_token' => $token]);
  }

  /**
   *  Called to make sure the credentials are valid
   *    - E.g mail, username, or password
   *    - no additional checks would also be valid
   *
   * @param mixed         $credentials
   * @param UserInterface $user
   *
   * @return bool
   */
  public function checkCredentials($credentials, UserInterface $user)
  {
    if ($user->getUsername() !== $credentials[self::USERNAME])
    {
      return false;
    }

    // return true to cause authentication success
    return true;
  }

  /**
   * @param Request        $request
   * @param TokenInterface $token
   * @param string         $providerKey
   *
   * @return Response|null
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
  {
    // on success, let the request continue
    return null;
  }

  /**
   * @param Request                 $request
   * @param AuthenticationException $exception
   *
   * @return JsonResponse|Response|null
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
  {
    $data = [
      $this->translator->trans($exception->getMessageKey(), $exception->getMessageData()),
    ];

    return new JsonResponse($data, Response::HTTP_FORBIDDEN);
  }

  /**
   * Called when authentication is needed, but it's not sent
   *
   * @param Request                      $request
   * @param AuthenticationException|null $authException
   *
   * @return JsonResponse|Response
   */
  public function start(Request $request, AuthenticationException $authException = null)
  {
    $data = [
      'Authentication Required',
    ];

    return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
  }

  /**
   * @return bool
   */
  public function supportsRememberMe()
  {
    return false;
  }


  /**
   * @param Request $request
   *
   * @return bool
   */
  private function requestHasValidAuthTokenInHeader(Request $request)
  {
    return $request->headers->has(self::TOKEN) && "" !== $request->headers->get(self::TOKEN);
  }

  /**
   * @param Request $request
   *
   * @return bool
   */
  private function postRequestHasValidCredentialsInRequestBody(Request $request)
  {
    return $request->isMethod(Request::METHOD_POST) &&
      $request->request->has(self::USERNAME) && "" !== $request->request->get(self::USERNAME);
  }

  /**
   * @param Request $request
   *
   * @return bool
   */
  private function getRequestHasValidCredentialsInQueryParameters(Request $request)
  {
    return $request->isMethod(Request::METHOD_GET) &&
      $request->query->has(self::USERNAME) && "" !== $request->query->get(self::USERNAME);
  }
}
