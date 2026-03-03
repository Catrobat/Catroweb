<?php

declare(strict_types=1);

namespace App\DB\Enum;

enum ReportCategory: string
{
  // Project categories
  case Copyright = 'copyright';
  case SexualContent = 'sexual_content';
  case GraphicViolence = 'graphic_violence';
  case HatefulContent = 'hateful_content';
  case ImproperRating = 'improper_rating';
  case Spam = 'spam';
  case Other = 'other';

  // Comment categories
  case Inappropriate = 'inappropriate';
  case Harassment = 'harassment';

  // User categories
  case Impersonation = 'impersonation';
  case InappropriateProfile = 'inappropriate_profile';
  case SpamAccount = 'spam_account';

  // Studio categories
  case InappropriateContent = 'inappropriate_content';

  /**
   * @return string[]
   */
  public static function getValidCategories(ContentType $content_type): array
  {
    return match ($content_type) {
      ContentType::Project => [
        self::Copyright->value,
        self::SexualContent->value,
        self::GraphicViolence->value,
        self::HatefulContent->value,
        self::ImproperRating->value,
        self::Spam->value,
        self::Other->value,
      ],
      ContentType::Comment => [
        self::Inappropriate->value,
        self::Spam->value,
        self::Harassment->value,
        self::Other->value,
      ],
      ContentType::User => [
        self::Impersonation->value,
        self::InappropriateProfile->value,
        self::SpamAccount->value,
        self::Harassment->value,
        self::Other->value,
      ],
      ContentType::Studio => [
        self::InappropriateContent->value,
        self::Spam->value,
        self::Copyright->value,
        self::Other->value,
      ],
    };
  }

  public static function isValidForContentType(string $category, ContentType $content_type): bool
  {
    return in_array($category, self::getValidCategories($content_type), true);
  }
}
