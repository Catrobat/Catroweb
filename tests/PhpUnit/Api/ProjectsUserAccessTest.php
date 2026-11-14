<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * GET /api/projects/user lists the caller's own projects. The public project-by-id access rule also matches
 * "user", which let anonymous callers through to the controller and answered 500 instead of 401.
 *
 * @internal
 *
 * @coversNothing
 */
class ProjectsUserAccessTest extends WebTestCase
{
  public function testOwnProjectsWithoutLoginAnswer401(): void
  {
    $client = static::createClient();

    $client->request('GET', '/api/projects/user?attributes=id,name&limit=5');
    $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());

    $client->request('GET', '/api/projects/user/');
    $this->assertSame(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
  }
}
