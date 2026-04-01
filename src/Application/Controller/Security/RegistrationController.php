<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\Security\ParentalConsent\ParentalConsentService;
use App\User\Achievements\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
  public function __construct(
    protected VerifyEmailHelperInterface $verify_email_helper,
    protected EntityManagerInterface $entity_manager,
    protected LoggerInterface $logger,
    protected AchievementManager $achievement_manager,
    protected VerifyEmail $verify_email,
    protected ParentalConsentService $parental_consent_service,
    protected RateLimiterFactory $parentPortalDailyLimiter,
  ) {
  }

  #[Route(path: '/register', name: 'register', methods: ['GET'])]
  public function register(): Response
  {
    return $this->render('Security/Registration/RegistrationPage.html.twig');
  }

  #[Route(path: '/verify', name: 'registration_confirmation_route', methods: ['GET'])]
  public function verifyUserEmail(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('index');
    }

    // Do not get the User's Id or Email Address from the Request object
    try {
      $this->verify_email_helper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
    } catch (VerifyEmailExceptionInterface $verifyEmailException) {
      $this->logger->critical('Email verification failed for '.$user->getId().$user->getEmail());
      $this->addFlash('verify_email_error', $verifyEmailException->getReason());

      return $this->redirectToRoute('register');
    }

    // Mark your user as verified. e.g. switch a User::verified property to true
    $user->setVerified(true);
    $this->entity_manager->persist($user);
    $this->entity_manager->flush();
    $this->achievement_manager->unlockAchievementAccountVerification($user);

    if ('pending' === $user->getConsentStatus() && null !== $user->getParentEmail()) {
      $this->parental_consent_service->sendConsentRequest($user);
    }

    return $this->redirectToRoute('index');
  }

  #[Route(path: '/verify-pending', name: 'verify_email_pending', methods: ['GET'])]
  public function verifyEmailPending(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user || $user->isVerified()) {
      return $this->redirectToRoute('index');
    }

    return $this->render('Security/Registration/VerifyEmailPending.html.twig');
  }

  #[Route(path: '/consent-pending', name: 'consent_pending', methods: ['GET'])]
  public function consentPending(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user instanceof User) {
      return $this->redirectToRoute('index');
    }

    $consentStatus = $user->getConsentStatus();
    if (!in_array($consentStatus, ['pending', 'revoked'], true)) {
      return $this->redirectToRoute('index');
    }

    return $this->render('Security/Registration/ConsentPending.html.twig', [
      'parent_email' => $user->getParentEmail(),
      'is_revoked' => 'revoked' === $consentStatus,
    ]);
  }

  #[Route(path: '/consent-pending/change-email', name: 'consent_change_parent_email', methods: ['POST'])]
  public function changeParentEmail(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user || 'pending' !== $user->getConsentStatus()) {
      return $this->redirectToRoute('index');
    }

    if (!$this->isCsrfTokenValid('change_parent_email', $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('consent_pending');
    }

    $limiter = $this->parentPortalDailyLimiter->create('change_parent_'.$user->getId());
    if (!$limiter->consume()->isAccepted()) {
      $this->addFlash('error', 'You can only change the parent email once per day. Please try again tomorrow.');

      return $this->redirectToRoute('consent_pending');
    }

    $newEmail = trim($request->request->getString('parent_email'));
    if ('' === $newEmail || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
      $this->addFlash('error', 'Please enter a valid email address.');

      return $this->redirectToRoute('consent_pending');
    }

    $userEmail = $user->getEmail();
    if (null !== $userEmail && mb_strtolower($newEmail) === mb_strtolower($userEmail)) {
      $this->addFlash('error', 'Parent email must be different from your own email address.');

      return $this->redirectToRoute('consent_pending');
    }

    $currentParentEmail = $user->getParentEmail();
    if (null !== $currentParentEmail && mb_strtolower($newEmail) === mb_strtolower($currentParentEmail)) {
      $this->addFlash('error', 'This is already the current parent email. Use the resend button instead.');

      return $this->redirectToRoute('consent_pending');
    }

    $user->setParentEmail($newEmail);
    $this->entity_manager->flush();
    $this->parental_consent_service->sendConsentRequest($user);

    $this->addFlash('success', 'Consent request sent to '.$newEmail);

    return $this->redirectToRoute('consent_pending');
  }

  #[Route(path: '/consent-pending/resend', name: 'consent_resend', methods: ['POST'])]
  public function resendConsentEmail(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user || 'pending' !== $user->getConsentStatus()) {
      return $this->redirectToRoute('index');
    }

    if (!$this->isCsrfTokenValid('resend_consent', $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('consent_pending');
    }

    $limiter = $this->parentPortalDailyLimiter->create('resend_consent_'.$user->getId());
    if (!$limiter->consume()->isAccepted()) {
      $this->addFlash('error', 'You can only resend once per day. Please try again tomorrow.');

      return $this->redirectToRoute('consent_pending');
    }

    $this->parental_consent_service->sendConsentRequest($user);
    $this->addFlash('success', 'Consent email resent to '.$user->getParentEmail());

    return $this->redirectToRoute('consent_pending');
  }

  #[Route(path: '/verify-pending/resend', name: 'verify_resend', methods: ['POST'])]
  public function resendVerifyEmail(Request $request): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user || $user->isVerified()) {
      return $this->redirectToRoute('index');
    }

    if (!$this->isCsrfTokenValid('resend_verify', $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('verify_email_pending');
    }

    $oneDayAgo = new \DateTime('-1 day');
    if ($user->getVerificationRequestedAt() > $oneDayAgo) {
      $this->addFlash('error', 'A verification email was already sent recently. Please check your inbox and spam folder.');

      return $this->redirectToRoute('verify_email_pending');
    }

    $this->verify_email->init($user)->send();
    $this->addFlash('success', 'Verification email resent. Please check your inbox.');

    return $this->redirectToRoute('verify_email_pending');
  }

  #[Route(path: '/verify', name: 'request_registration_confirmation_route', methods: ['POST'])]
  public function requestVerifyUserEmail(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
    }

    $oneDayAgo = new \DateTime('-1 day');
    if ($user->getVerificationRequestedAt() > $oneDayAgo) {
      return new JsonResponse(null, Response::HTTP_FORBIDDEN);
    }

    $this->verify_email->init($user)->send();

    return new JsonResponse(null, Response::HTTP_NO_CONTENT);
  }
}
