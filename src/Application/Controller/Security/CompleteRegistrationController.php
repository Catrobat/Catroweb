<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use App\Api\Services\User\UserRequestValidator;
use App\DB\Entity\User\User;
use App\Security\ParentalConsent\ParentalConsentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CompleteRegistrationController extends AbstractController
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ParentalConsentService $consent_service,
  ) {
  }

  #[Route(path: '/complete-registration', name: 'complete_registration', methods: ['GET'])]
  public function show(): Response
  {
    $user = $this->getUser();
    if (!$user instanceof User || null !== $user->getDateOfBirth()) {
      return $this->redirectToRoute('index');
    }

    return $this->render('Security/CompleteRegistration.html.twig');
  }

  #[Route(path: '/complete-registration', name: 'complete_registration_submit', methods: ['POST'])]
  public function submit(Request $request): Response
  {
    $user = $this->getUser();
    if (!$user instanceof User) {
      return $this->redirectToRoute('login');
    }

    if (null !== $user->getDateOfBirth()) {
      return $this->redirectToRoute('index');
    }

    if (!$this->isCsrfTokenValid('complete_registration', $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('complete_registration');
    }

    $dobString = $request->request->getString('date_of_birth');
    $dobImmutable = \DateTimeImmutable::createFromFormat('Y-m-d|', $dobString);
    if (false === $dobImmutable) {
      $this->addFlash('error', 'Please enter a valid date of birth.');

      return $this->redirectToRoute('complete_registration');
    }

    $age = $dobImmutable->diff(new \DateTimeImmutable('today'))->y;
    if ($age < 3) {
      $this->addFlash('error', 'Please enter a valid date of birth.');

      return $this->redirectToRoute('complete_registration');
    }

    $dob = \DateTime::createFromImmutable($dobImmutable);
    $user->setDateOfBirth($dob);
    $user->setMinor($age < 16);

    $needsConsent = $age < UserRequestValidator::PARENTAL_CONSENT_AGE;

    if ($needsConsent) {
      $parentEmail = $request->request->getString('parent_email');
      if ('' === $parentEmail || !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
        $this->addFlash('error', 'A valid parent or guardian email is required for users under 14.');

        return $this->redirectToRoute('complete_registration');
      }

      $userEmail = $user->getEmail();
      if (null !== $userEmail && mb_strtolower($parentEmail) === mb_strtolower($userEmail)) {
        $this->addFlash('error', 'Parent email must be different from your own email address.');

        return $this->redirectToRoute('complete_registration');
      }

      $user->setConsentStatus('pending');
      $user->setParentEmail($parentEmail);
    } else {
      $user->setConsentStatus('not_required');
    }

    $this->entity_manager->flush();

    if ($needsConsent) {
      $this->consent_service->sendConsentRequest($user);
    }

    return $this->redirectToRoute('index');
  }
}
