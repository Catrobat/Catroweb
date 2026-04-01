<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\UserRepository;
use App\Security\ParentalConsent\ParentalConsentService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class ParentalConsentController extends AbstractController
{
  public function __construct(
    private readonly VerifyEmailHelperInterface $verify_email_helper,
    private readonly ParentalConsentService $consent_service,
    private readonly UserRepository $user_repository,
    private readonly LoggerInterface $logger,
  ) {
  }

  #[Route(path: '/consent/confirm', name: 'parental_consent_confirm', methods: ['GET'])]
  public function confirmConsent(Request $request): Response
  {
    $id = $request->query->get('id');
    if (null === $id) {
      $this->addFlash('error', 'Invalid consent link.');

      return $this->redirectToRoute('index');
    }

    $user = $this->user_repository->find($id);
    if (!$user instanceof User) {
      $this->addFlash('error', 'User not found.');

      return $this->redirectToRoute('index');
    }

    try {
      $this->verify_email_helper->validateEmailConfirmation(
        $request->getUri(),
        (string) $user->getId(),
        $user->getParentEmail() ?? ''
      );
    } catch (VerifyEmailExceptionInterface $e) {
      $this->logger->warning('Parental consent verification failed: '.$e->getReason().' URI: '.$request->getUri().' User: '.$user->getId().' Email: '.$user->getParentEmail());
      $this->addFlash('error', 'This consent link is invalid or has expired. Please request a new one from the consent-pending page.');

      return $this->redirectToRoute('index');
    }

    $this->consent_service->confirmConsent($user, $request->getClientIp());

    return $this->render('Security/ParentPortal/ConsentConfirmed.html.twig', [
      'username' => $user->getUsername(),
    ]);
  }
}
