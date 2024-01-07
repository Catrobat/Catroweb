<?php

namespace App\Api_deprecated\Security;

use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class ApiTokenAuthenticator extends AbstractAuthenticator
{
  /**
   * @required request parameter TOKEN
   *
   *  Must be sent in the request HEADER containing the user token
   *  Must not be empty
   */
  private const TOKEN = 'authenticate';
  private const OLD_TOKEN = 'token';

  public function __construct(
    private readonly EntityManagerInterface $em,
    protected TranslatorInterface $translator
  ) {
  }

  /**
   * Called on every request to decide if this authenticator should be
   * used for the request. Returning false will cause this authenticator
   * to be skipped.
   *
   * {@inheritdoc}
   */
  public function supports(Request $request): ?bool
  {
    return $this->requestHasValidAuthTokenInHeader($request)
      || $this->requestHasValidTokenInBody__supportAPIv1($request);
  }

  /**
   * @throws NonUniqueResultException
   */
  public function authenticate(Request $request): Passport
  {
    $token = $request->headers->has(self::TOKEN) ? $request->headers->get(self::TOKEN) : $request->request->get(self::OLD_TOKEN);

    if (null === $token || '' === $token) {
      throw new AuthenticationException('Empty token!');
    }

    $qb = $this->em->createQueryBuilder();
    $qb->select('u.username')
      ->from(User::class, 'u')
      ->where('u.upload_token = :token')
      ->setParameter('token', $token)
    ;
    $user = $qb->getQuery()->getOneOrNullResult();

    if (!$user) {
      throw new AuthenticationException('User not found!');
    }

    return new SelfValidatingPassport(new UserBadge($user['username']));
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
  {
    // on success, let the request continue
    return null;
  }

  public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
  {
    $data = [
      $this->translator->trans($exception->getMessageKey(), $exception->getMessageData()),
    ];

    return new JsonResponse($data, Response::HTTP_FORBIDDEN);
  }

  private function requestHasValidAuthTokenInHeader(Request $request): bool
  {
    return $request->headers->has(self::TOKEN) && '' !== $request->headers->get(self::TOKEN);
  }

  private function requestHasValidTokenInBody__supportAPIv1(Request $request): bool
  {
    return $request->request->has(self::OLD_TOKEN) && '' !== $request->request->get(self::OLD_TOKEN);
  }
}
