<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

class TranschoiceTest extends \PHPUnit\Framework\TestCase
{

  const LANGUAGE_DIR = './translations/';

  /**
   * @test
   * @dataProvider language_provider
   */
  public function all_transchoice_entries_should_have_a_correct_syntax(Translator $translator, $language_code, $message_ids)
  {
    foreach ($message_ids as $message_id)
    {
      $translator->transChoice($message_id, 1, [], 'catroweb', $language_code);
      $translator->transChoice($message_id, 2, [], 'catroweb', $language_code);
      $translator->transChoice($message_id, 10, [], 'catroweb', $language_code);
    }
    $this->assertTrue(true);
  }

  public function language_provider()
  {
    $directory = self::LANGUAGE_DIR;

    $translator = new Translator('en', new MessageFormatter());
    $translator->addLoader('yaml', new YamlFileLoader());

    $finder = new Finder();
    $files = $finder->in($directory)->files();
    $language_codes = [];
    foreach ($files as $file)
    {
      $parts = explode(".", $file->getFilename());
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

  private function getLanguageCodesFromFilenames()
  {
    $finder = new Finder();
    $directory = self::LANGUAGE_DIR;
    $files = $finder->in($directory)->files();
    $language_codes = [];
    foreach ($files as $file)
    {
      $parts = explode(".", $file->getFilename());
      $language_codes[] = $parts[1];
    }

    return $language_codes;
  }
}
