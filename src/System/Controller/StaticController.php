<?php

declare(strict_types=1);

namespace App\System\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class StaticController extends AbstractController
{
  public function robotsTxt(): Response
  {
    return new Response(
      $this->renderView('Static/robots.txt.twig'),
      Response::HTTP_OK,
      ['Content-Type' => 'text/plain']
    );
  }
}
