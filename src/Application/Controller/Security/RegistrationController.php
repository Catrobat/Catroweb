<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use App\DB\Entity\User\User;
use App\Security\Authentication\VerifyEmail;
use App\User\Achievements\AchievementManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
  public function __construct(protected VerifyEmailHelperInterface $verify_email_helper, protected EntityManagerInterface $entity_manager, protected LoggerInterface $logger, protected AchievementManager $achievement_manager, protected VerifyEmail $verify_email)
  {
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
    $this->addFlash('success', 'Your e-mail address has been verified.');

    return $this->redirectToRoute('index');
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
