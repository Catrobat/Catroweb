<?php

namespace App\Catrobat\Translate;

use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorAwareTrait
{
  private TranslatorInterface $translator;

  public function initTranslator(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  public function __(string $id, array $parameter = [], ?string $locale = null): string
  {
    try {
      return $this->translator->trans($id, $parameter, 'catroweb', $locale);
    } catch (Exception $e) {
      return $this->translator->trans($id, $parameter, 'catroweb', 'en');
    }
  }
}
