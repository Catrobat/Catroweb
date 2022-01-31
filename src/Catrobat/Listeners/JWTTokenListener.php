<?php

namespace App\Catrobat\Listeners;

use App\Manager\UserManager;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class JWTTokenListener
{
  private RefreshTokenManagerInterface $refresh_manager;
  private UserManager $user_manager;
  private JWTTokenManagerInterface $jwt_manager;

  public function __construct(RefreshTokenManagerInterface $refresh_manager, UserManager $user_manager,
                              JWTTokenManagerInterface $jwt_manager)
  {
    $this->refresh_manager = $refresh_manager;
    $this->user_manager = $user_manager;
    $this->jwt_manager = $jwt_manager;
  }

  public function onKernelRequest(RequestEvent $event): void
  {
    if ($event->getRequest()->cookies->has('BEARER')) {
      $bearer_token = $event->getRequest()->cookies->get('BEARER');
      $event->getRequest()->headers->set('Authorization', 'Bearer '.$bearer_token);
    }

    if ($event->getRequest()->cookies->has('REFRESH_TOKEN')) {
      $x_refresh = $event->getRequest()->cookies->get('REFRESH_TOKEN');
      $event->getRequest()->headers->set('X-Refresh', $x_refresh);

      if (!$event->getRequest()->cookies->has('BEARER')) {
        $refresh_token = $this->refresh_manager->get($x_refresh);
        if (null !== $refresh_token && $refresh_token->isValid()) {
          $user = $this->user_manager->findUserByUsername($refresh_token->getUsername());
          if (null !== $user) {
            $event->getRequest()->headers->set('Authorization', 'Bearer '.$this->jwt_manager->create($user));
          } else {
            $this->refresh_manager->delete($refresh_token);
          }
        }
      }
    }
  }
}
