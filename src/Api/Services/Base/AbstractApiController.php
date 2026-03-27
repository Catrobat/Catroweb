<?php

declare(strict_types=1);

namespace App\Api\Services\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractApiController extends AbstractController implements BearerAuthenticationInterface
{
  use BearerAuthenticationTrait;

  private ?RequestStack $request_stack = null;

  #[Required]
  public function setRequestStack(RequestStack $request_stack): void
  {
    $this->request_stack = $request_stack;
  }

  protected function getCurrentRequest(): ?Request
  {
    return $this->request_stack?->getCurrentRequest();
  }
}
