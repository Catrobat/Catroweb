<?php

declare(strict_types=1);

namespace App\Moderation;

/**
 * Detects "adults only" signals ("18+", "+18", "nsfw", "Ab 18", "solo adultos", "только для взрослых", …) in
 * user texts. They are not bad words (TextSanitizer leaves them alone) but a strong hint that the content is
 * not for kids, so a match flags the content for review instead of censoring it.
 *
 * Numbers that only look like an age are removed first: versions and levels ("1.18+", "version 18+",
 * "level 18+"), years ("2018") and sums ("18+2"). A match is ignored when negated ("no 18+", "kein NSFW"),
 * when it counts something ("18+ levels", "ab 18 Punkten"), after "Score:" or "Seite", and in phone numbers.
 */
class AgeSignalDetector
{
  /** "version 18+", "lvl 18", "v1.18+", "Android 18+", …: a counter in front of the number. */
  private const string NUMBER_CONTEXT = '~(*UCP)\b(?:version|ver|v|level|lvl|android|api|ios|stufe|nivel|nível|уровень|'
    .'chapter|kapitel|part|teil|episode|ep|page|seite|day|tag|wave|round|runde|stage|world|welt)\.?\s*\d[\d.]*\s*\+?~iu';

  /** "1 + 18", "1  + 18", "(a + b) + 18", "x + 18 = 20", "18 + 2 = 20", …: sums and arithmetic equations. */
  private const string SUM_CONTEXT = '~(*UCP)(?<![\p{L}\p{N}])\d[\d.]*\s*\+\s*18(?!\p{N})'
    .'|\)\s*\+\s*18(?!\p{N})'
    .'|(?:\b\w+\s*\+\s*18|(?<![\p{L}\p{N}])18\s*\+\s*[\w.]+|\+?\s*18)\s*=\s*[\d.]+~iu';

  /** Right before a match: negations ("no 18+", "kein NSFW") and counters ("Score: 18+", "Seite ab 18"). */
  private const string IGNORED_BEFORE = '~(*UCP)\b(?:no|not|never|kein\w*|nicht|nie|sin|sem|без|нет|не|pas\s+de|non|'
    .'score|punktzahl|seite|page|p[aá]gina|страниц\w*)\s*[:\-]?\s*$~iu';

  private const string COUNTED_NOUNS = 'levels?|kills?|times?|points?|pts|coins?|stars?|lives|enemies|'
    .'items?|players?|rounds?|seconds?|minutes?|hours?|blocks?|bricks?|sprites?|looks?|sounds?|followers?|likes?|'
    .'downloads?|views?|hp|xp|damage|dmg|boost|speed|grip|power|fuel|health|armou?r|energy|gems?|force|atk|def|'
    .'punkte?n?|stufen|leben|sterne?n?|münzen|spieler\w*|gegner\w*|mal|sekunden|minuten|'
    .'niveles|puntos|monedas|vidas|estrellas|jugadores|enemigos|veces|'
    .'n[ií]veis|pontos|moedas|estrelas|jogadores|inimigos|vezes|'
    .'уровн\w*|очк\w*|монет\w*|жизн\w*|звезд\w*|игрок\w*|раз';

  /**
   * Right after a match: a counted noun ("18+ levels", "ab 18 Punkten") or more digits (a phone number).
   * Not "projects", "games" or "content": "+18 projects" is a signal.
   */
  private const string IGNORED_AFTER = '~(*UCP)^(?:[\s\-]*\d++(?![\s\-]*(?:'.self::COUNTED_NOUNS.')\b)|\s*(?:'.self::COUNTED_NOUNS.')\b)~iu';

  /**
   * @var list<string>
   */
  private const array PATTERNS = [
    // 18+ / 18 + / +18 / + 18 as a standalone number, not part of a version, year, phone number or sum
    '(?<![\p{L}\p{N}.,:/\-])(?<!\d\s)18\s?\+(?!\d)',
    '(?<![\p{L}\p{N}.,:/\-])(?<!\d\s)\+\s?18(?!\p{N}|[.,]\d)',
    // R18, R-18, FSK 18, USK 18, PEGI 18
    '\b(?:r-?|fsk\s?|usk\s?|pegi\s?)18\b',
    '(?<![\p{L}\p{N}])nsfw(?![\p{L}\p{N}])', // also in user_names
    // en: 18 plus, 18 and over/up, 18 or older, adults only, adult content, older than 18
    '\b18\s*(?:years?\s*)?(?:plus|and\s+(?:over|up|older)|or\s+older)\b',
    '\b(?:adults?\s+only|only\s+(?:for\s+)?adults|adult\s+content|content\s+for\s+adults|older\s+than\s+18)\b',
    // de: ab 18 (not "ab 18 Uhr"), nur für Erwachsene, 18 Jahre und älter
    '\bab\s+18\b(?!\s*(?:uhr|h\b|:|\.\d))',
    '\b(?:nur\s+f(?:ü|ue)r\s+erwachsene|erwachseneninhalte?|18\s+jahre?n?\s+und\s+(?:ä|ae)lter)\b',
    // es: solo (para) adultos, mayores de 18, contenido (para) adultos
    '\b(?:s[oó]lo\s+(?:para\s+)?(?:adultos|mayores)|mayores\s+de\s+(?:18|edad)|contenido\s+(?:para\s+)?adultos?|18\s+(?:años\s+)?(?:o|y)\s+m[aá]s)\b',
    // pt: só/apenas/somente (para) adultos, maiores de 18, conteúdo adulto, proibido para menores
    '\b(?:(?:s[oó]|apenas|somente)\s+(?:para\s+)?(?:adultos|maiores)|maiores\s+de\s+18|conte[uú]do\s+(?:para\s+)?adultos?|proibido\s+para\s+menores|18\s+anos\s+ou\s+mais)\b',
    // ru: для взрослых, старше 18, 18 лет и старше
    '\b(?:только\s+)?для\s+взрослых\b|\bстарше\s+18\b|\b18\s+лет\s+и\s+старше\b',
    // fr/it: pour adultes, réservé aux adultes, interdit aux moins de 18 ans, solo per adulti, vietato ai minori
    '\b(?:pour\s+adultes|r[ée]serv[ée]\s+aux\s+adultes|interdit\s+aux\s+moins\s+de\s+18|solo\s+per\s+adulti|vietato\s+ai\s+minori)\b',
  ];

  private const string INVISIBLE_CHARACTERS = '/[\x{200B}\x{200C}\x{200D}\x{2060}\x{00AD}\x{FEFF}]/u';

  public static function removeInvisibleCharacters(string $text): string
  {
    return preg_replace(self::INVISIBLE_CHARACTERS, '', $text) ?? $text;
  }

  /**
   * @return string|null the first signal found, for the moderation note
   */
  public function findSignal(?string ...$texts): ?string
  {
    foreach ($texts as $text) {
      if (null === $text || '' === $text) {
        continue;
      }

      $text = self::removeInvisibleCharacters($text);
      $text = preg_replace(self::NUMBER_CONTEXT, ' ', $text) ?? $text;
      $text = preg_replace(self::SUM_CONTEXT, ' ', $text) ?? $text;
      foreach (self::PATTERNS as $pattern) {
        preg_match_all('~(*UCP)'.$pattern.'~iu', $text, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as [$match, $offset]) {
          if (1 !== preg_match(self::IGNORED_BEFORE, substr($text, 0, $offset))
            && 1 !== preg_match(self::IGNORED_AFTER, substr($text, $offset + strlen($match)))) {
            return trim($match);
          }
        }
      }
    }

    return null;
  }
}
