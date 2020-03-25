<?php

namespace Tests\phpUnit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorBagInterface;

/**
 * @internal
 * @coversNothing
 */
class TranschoiceTest extends TestCase
{
  /**
   * @var string
   */
  const LANGUAGE_DIR = './translations/';

  /**
   * @test
   * @dataProvider language_provider
   *
   * @param mixed $language_code
   * @param mixed $message_ids
   */
  public function allTranschoiceEntriesShouldHaveACorrectSyntax(TranslatorBagInterface $translator, $language_code, $message_ids): void
  {
    foreach ($message_ids as $message_id)
    {
      $translator->trans($message_id, ['%count%' => 1], 'catroweb', $language_code);
      $translator->trans($message_id, ['%count%' => 2], 'catroweb', $language_code);
      $translator->trans($message_id, ['%count%' => 10], 'catroweb', $language_code);
    }
    $this->assertTrue(true);
  }

  public function language_provider(): array
  {
    $directory = self::LANGUAGE_DIR;

    $translator = new Translator('en', new MessageFormatter());
    $translator->addLoader('yaml', new YamlFileLoader());

    $finder = new Finder();
    $files = $finder->in($directory)->files();
    $language_codes = [];
    foreach ($files as $file)
    {
      $parts = explode('.', $file->getFilename());
      $language_codes[] = $parts[1];
      $translator->addResource('yaml', $file, $parts[1], 'catroweb');
    }

    $translator->setFallbackLocales([
      'en',
    ]);

    $catalogue = $translator->getCatalogue('en');
    $messages = $catalogue->all();
    $message_ids = array_keys($messages['catroweb']);

    $data = [];
    foreach ($language_codes as $language_code)
    {
      $data[] = [
        $translator,
        $language_code,
        $message_ids,
      ];
    }

    return $data;
  }
}
