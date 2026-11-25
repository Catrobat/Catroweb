<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\Application\Controller\Search\SearchController;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/** @internal */
#[CoversClass(SearchController::class)]
final class SearchControllerTest extends KernelTestCase
{
  public function testTheDocsHomeFormUsesTheExistingSearchResultsPage(): void
  {
    $kernel = self::bootKernel();
    $docs = $kernel->handle(Request::create('/app/search/?q=+cat+jump+&context=docs&scope=docs&locale=de'));
    $this->assertSame(Response::HTTP_OK, $docs->getStatusCode());
    $this->assertStringContainsString('data-query="cat jump"', (string) $docs->getContent());
    $this->assertStringContainsString('data-docs-only="true"', (string) $docs->getContent());
    $this->assertStringContainsString('data-docs-explicit-locale="de"', (string) $docs->getContent());

    $this->assertStringContainsString('data-trans-docs="Dokumentation"', (string) $docs->getContent());

    $general = $kernel->handle(Request::create('/app/search/cat?context=docs'));
    $this->assertSame(Response::HTTP_OK, $general->getStatusCode());
    $this->assertStringContainsString('data-docs-only="false"', (string) $general->getContent());
    $this->assertStringContainsString('data-query="cat"', (string) $general->getContent());
  }
}
