<?php

namespace App\Application\Controller\Security;

use App\Application\Form\ChangePasswordFormType;
use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[Route(path: '/reset-password')]
class ResetPasswordController extends AbstractController
{
  use ResetPasswordControllerTrait;

  public function __construct(protected VerifyEmailHelperInterface $verify_email_helper, private readonly ResetPasswordHelperInterface $resetPasswordHelper, private readonly EntityManagerInterface $entityManager)
  {
  }

  /**
   * Display the request for a password reset.
   */
  #[Route(path: '', name: 'app_forgot_password_request')]
    public function request(): Response
    {
      return $this->render('security/reset_password/request.html.twig', []);
    }

  #[Route(path: '/email', name: 'app_email')]
    public function testEmail(): Response 
    {
      
      // Generate a fake token if the user does not exist or someone hit this page directly.
      // This prevents exposing whether a user was found with the given email address or not
      if (null === ($resetToken = $this->getTokenObjectFromSession())) {
        $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
      }

      $signature = $this->verify_email_helper->generateSignature(
        'registration_confirmation_route',
        'user_id',
        'user@email.com'
      );

      $expirationTime = $signature->getExpiresAt();

      $confirm = "this is the url";

      $user = array(
        'username' => 'unprepared'
      );

      $message = "Hello you need to come to the catrobat building as fast as possible. there is something wrong with your project please come as fast as u can";
      $titel = "Der titel";
      $subject = "Das subject";
      $wrapped_message = wordwrap($message, 60, "<br>\n");

      return $this->render('security/reset_password/email.html.twig', [
        'resetToken' => $resetToken,
        'user' => $user,
        'signedUrl' => $confirm,
        'deleteUrl' => $confirm,
        'message' => $wrapped_message,
        'titel' => $titel,
        'head' => $subject,
        'expire' => $expirationTime->format('H:i')
      ]);
    }


  /**
   * Confirmation page after a user has requested a password reset.
   */
  #[Route(path: '/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
      // Generate a fake token if the user does not exist or someone hit this page directly.
      // This prevents exposing whether a user was found with the given email address or not
      if (null === ($resetToken = $this->getTokenObjectFromSession())) {
        $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
      }

      return $this->render('security/reset_password/check_email.html.twig', [
        'resetToken' => $resetToken,
      ]);
    }

  /**
   * Validates and process the reset URL that the user clicked in their email.
   */
  #[Route(path: '/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $user_password_hasher, string $token = null): Response
    {
      if ($token) {
        // We store the token in session and remove it from the URL, to avoid the URL being
        // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
        $this->storeTokenInSession($token);

        return $this->redirectToRoute('app_reset_password');
      }
      $token = $this->getTokenFromSession();
      if (null === $token) {
        throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
      }
      try {
        /** @var User $user */
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
      } catch (ResetPasswordExceptionInterface $e) {
        $this->addFlash('reset_password_error', sprintf(
          'There was a problem validating your reset request - %s',
          $e->getReason()
        ));

        return $this->redirectToRoute('app_forgot_password_request');
      }
      // The token is valid; allow the user to change their password.
      $form = $this->createForm(ChangePasswordFormType::class);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        // A password reset token should be used only once, remove it.
        $this->resetPasswordHelper->removeResetRequest($token);

        // Encode(hash) the plain password, and set it.
        $hashedPassword = $user_password_hasher->hashPassword($user, $form->get('plainPassword')->getData());

        $user->setPassword($hashedPassword);
        $this->entityManager->flush();

        // The session is cleaned up after the password has been changed.
        $this->cleanSessionAfterReset();

        return $this->redirectToRoute('index');
      }

      return $this->render('security/reset_password/reset.html.twig', [
        'resetForm' => $form->createView(),
      ]);
    }
}
