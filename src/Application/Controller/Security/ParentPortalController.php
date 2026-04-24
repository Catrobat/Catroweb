<?php

declare(strict_types=1);

namespace App\Application\Controller\Security;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\UserRepository;
use App\Security\Captcha\CaptchaVerifier;
use App\Security\ParentalConsent\ParentalConsentService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface as RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class ParentPortalController extends AbstractController
{
  public function __construct(
    private readonly ParentalConsentService $consent_service,
    private readonly VerifyEmailHelperInterface $verify_email_helper,
    private readonly UserRepository $user_repository,
    private readonly CaptchaVerifier $captcha_verifier,
    private readonly LoggerInterface $logger,
    private readonly RateLimiterFactory $parentPortalDailyLimiter,
  ) {
  }

  #[Route(path: '/parent-info', name: 'parent_info', methods: ['GET'])]
  public function info(): Response
  {
    return $this->render('Security/ParentPortal/InfoPage.html.twig');
  }

  #[Route(path: '/parent', name: 'parent_portal', methods: ['GET'])]
  public function portal(): Response
  {
    return $this->render('Security/ParentPortal/PortalPage.html.twig');
  }

  #[Route(path: '/parent/send-link', name: 'parent_send_link', methods: ['POST'])]
  public function sendLink(Request $request): Response
  {
    $data = json_decode($request->getContent(), true) ?? [];
    $email = trim((string) ($data['email'] ?? ''));

    if ('' === $email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return $this->json(['email' => 'Please enter a valid email address.'], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    $ip = $request->getClientIp() ?? 'unknown';

    $limiter = $this->parentPortalDailyLimiter->create($ip);
    if (!$limiter->consume()->isAccepted()) {
      return new Response(null, Response::HTTP_TOO_MANY_REQUESTS);
    }

    $captchaResult = $this->captcha_verifier->verify($data['captcha_token'] ?? '', $ip);
    if (!$captchaResult['success']) {
      return new Response(null, Response::HTTP_FORBIDDEN);
    }

    // Always return success to avoid email enumeration
    $this->consent_service->sendManagementLink($email);

    return new Response(null, Response::HTTP_NO_CONTENT);
  }

  #[Route(path: '/parent/manage', name: 'parent_management', methods: ['GET'])]
  public function manage(Request $request): Response
  {
    try {
      $this->verify_email_helper->validateEmailConfirmation(
        $request->getUri(),
        'parent',
        $request->query->get('email', '')
      );
    } catch (VerifyEmailExceptionInterface $e) {
      $this->logger->warning('Parent management link verification failed: '.$e->getReason());
      $this->addFlash('error', 'This link is invalid or has expired. Please request a new one.');

      return $this->redirectToRoute('parent_portal');
    }

    $parent_email = $request->query->getString('email');
    $children = $this->consent_service->getChildrenByParentEmail($parent_email);

    if ([] === $children) {
      $this->addFlash('error', 'No accounts found for this email address.');

      return $this->redirectToRoute('parent_portal');
    }

    return $this->render('Security/ParentPortal/ManagePage.html.twig', [
      'children' => $children,
      'parent_email' => $parent_email,
    ]);
  }

  #[Route(path: '/parent/revoke/{id}', name: 'parent_revoke_consent', methods: ['POST'])]
  public function revokeConsent(Request $request, string $id): Response
  {
    if (!$this->isCsrfTokenValid('parent_revoke_'.$id, $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('parent_portal');
    }

    $user = $this->user_repository->find($id);
    if (!$user instanceof User) {
      $this->addFlash('error', 'Account not found.');

      return $this->redirectToRoute('parent_portal');
    }

    $parentEmail = $user->getParentEmail() ?? '';
    $this->consent_service->revokeConsent($user, $request->getClientIp());
    $children = $this->consent_service->getChildrenByParentEmail($parentEmail);
    $this->addFlash('success', 'Consent has been revoked for '.$user->getUsername().'. The account now has limited functionality.');

    return $this->render('Security/ParentPortal/ManagePage.html.twig', [
      'children' => $children,
      'parent_email' => $parentEmail,
    ]);
  }

  #[Route(path: '/parent/grant/{id}', name: 'parent_grant_consent', methods: ['POST'])]
  public function grantConsent(Request $request, string $id): Response
  {
    if (!$this->isCsrfTokenValid('parent_grant_'.$id, $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('parent_portal');
    }

    $user = $this->user_repository->find($id);
    if (!$user instanceof User) {
      $this->addFlash('error', 'Account not found.');

      return $this->redirectToRoute('parent_portal');
    }

    $parentEmail = $user->getParentEmail() ?? '';
    $this->consent_service->confirmConsent($user, $request->getClientIp());
    $children = $this->consent_service->getChildrenByParentEmail($parentEmail);
    $this->addFlash('success', 'Consent has been granted for '.$user->getUsername().'.');

    return $this->render('Security/ParentPortal/ManagePage.html.twig', [
      'children' => $children,
      'parent_email' => $parentEmail,
    ]);
  }

  #[Route(path: '/parent/delete/{id}', name: 'parent_delete_account', methods: ['POST'])]
  public function deleteAccount(Request $request, string $id): Response
  {
    if (!$this->isCsrfTokenValid('parent_delete_'.$id, $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('parent_portal');
    }

    $user = $this->user_repository->find($id);
    if (!$user instanceof User) {
      $this->addFlash('error', 'Account not found.');

      return $this->redirectToRoute('parent_portal');
    }

    $username = $user->getUsername();
    $parentEmail = $user->getParentEmail() ?? '';
    $this->consent_service->deleteChildAccount($user, $request->getClientIp());
    $children = $this->consent_service->getChildrenByParentEmail($parentEmail);
    $this->addFlash('success', 'The account for '.$username.' has been permanently deleted.');

    return $this->render('Security/ParentPortal/ManagePage.html.twig', [
      'children' => $children,
      'parent_email' => $parentEmail,
    ]);
  }

  #[Route(path: '/parent/export/{id}', name: 'parent_request_export', methods: ['POST'])]
  public function requestExport(Request $request, string $id): Response
  {
    if (!$this->isCsrfTokenValid('parent_export_'.$id, $request->request->getString('_csrf_token'))) {
      $this->addFlash('error', 'Invalid request. Please try again.');

      return $this->redirectToRoute('parent_portal');
    }

    $user = $this->user_repository->find($id);
    if (!$user instanceof User) {
      $this->addFlash('error', 'Account not found.');

      return $this->redirectToRoute('parent_portal');
    }

    $this->consent_service->logAction($user, 'data_export_requested', $user->getParentEmail() ?? '', $request->getClientIp());

    $export = [
      'exported_at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
      'profile' => [
        'username' => $user->getUsername(),
        'email' => $user->getEmail(),
        'about' => $user->getAbout(),
        'date_of_birth' => $user->getDateOfBirth()?->format('Y-m-d'),
        'created_at' => $user->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        'is_minor' => $user->isMinor(),
        'consent_status' => $user->getConsentStatus(),
      ],
      'projects' => array_map(static fn ($p) => [
        'id' => $p->getId(),
        'name' => $p->getName(),
        'description' => $p->getDescription(),
        'uploaded_at' => $p->getUploadedAt()?->format(\DateTimeInterface::ATOM),
      ], $user->getProjects()->toArray()),
    ];

    $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '{}';

    return new Response($json, Response::HTTP_OK, [
      'Content-Type' => 'application/json',
      'Content-Disposition' => 'attachment; filename="catrobat-data-export-'.$user->getUsername().'.json"',
    ]);
  }
}
