<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Application\Locale;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class TransChoiceTest extends TestCase
{
  public const string LANGUAGE_DIR = 'tests/TestData/DataFixtures/translations/';

  #[DataProvider('provideLanguageData')]
  public function testAllTransChoiceEntriesShouldHaveACorrectSyntax(TranslatorInterface $translator, string $language_code, array $message_ids): void
  {
    $this->expectNotToPerformAssertions();
    foreach ($message_ids as $message_id) {
      $translator->trans($message_id, ['%count%' => 1], 'catroweb', $language_code);
      $translator->trans($message_id, ['%count%' => 2], 'catroweb', $language_code);
      $translator->trans($message_id, ['%count%' => 10], 'catroweb', $language_code);
    }
  }

  public static function provideLanguageData(): array
  {
    $directory = self::LANGUAGE_DIR;

    $translator = new Translator('en', new MessageFormatter());
    $translator->addLoader('yaml', new YamlFileLoader());

    $finder = new Finder();
    $files = $finder->in($directory)->files();
    $language_codes = [];
    foreach ($files as $file) {
      $parts = explode('.', $file->getFilename());
      $language_codes[] = $parts[1];
      $translator->addResource('yaml', $file, $parts[1], 'catroweb');
    }

    $translator->setFallbackLocales([
      'en',
    ]);

    $catalogue = $translator->getCatalogue('en');
    $messages = $catalogue->all();
    $message_ids = array_keys($messages['catroweb'] ?? []);

    $data = [];
    foreach ($language_codes as $language_code) {
      $data[] = [
        $translator,
        $language_code,
        $message_ids,
      ];
    }

    return $data;
  }
}
