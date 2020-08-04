<?php

namespace App\Catrobat\Services\TestEnv\DataFixtures;

use App\Entity\User;
use App\Entity\UserManager;
use App\Utils\MyUuidGenerator;
use PHPUnit\Framework\Assert;

/**
 * Class UserDataFixtures.
 *
 * Use this class in the test environment to easily create new users in the database.
 */
class UserDataFixtures
{
  private UserManager $user_manager;

  private static ?User $default_user = null;

  private static ?User $current_user = null;

  private static int $number_of_users = 0;

  public function __construct(UserManager $user_manager)
  {
    $this->user_manager = $user_manager;
  }

  public function insertUser(array $config = [], bool $andFlush = true): User
  {
    if (array_key_exists('id', $config))
    {
      // use a fixed ID
      MyUuidGenerator::setNextValue($config['id']);
    }

    ++UserDataFixtures::$number_of_users;

    /** @var User $user */
    $user = $this->user_manager->createUser();
    $user->setUsername($config['name'] ?? 'User'.UserDataFixtures::$number_of_users);
    $user->setEmail($config['email'] ?? $user->getUsername().'@catrobat.at');
    $user->setPlainPassword($config['password'] ?? '123456');
    $user->setUploadToken($config['token'] ?? 'default_token_'.UserDataFixtures::$number_of_users);
    $user->setSuperAdmin(isset($config['admin']) ? 'true' === $config['admin'] : false);
    $user->setAdditionalEmail($config['additional_email'] ?? '');
    $user->setEnabled(isset($config['enabled']) ? 'true' === $config['enabled'] : true);
    $user->setCountry($config['country'] ?? 'at');
    $user->addRole($config['role'] ?? 'ROLE_USER');
    $user->setOauthUser(isset($config['oauth_user']) ? 'true' === $config['oauth_user'] : false);
    $this->user_manager->updateUser($user, $andFlush);

    return $user;
  }

  public function assertUser(array $config = []): void
  {
    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($config['name']);

    Assert::assertNotNull($user);

    if (isset($config['name']))
    {
      Assert::assertEquals($user->getUsername(), $config['name'],
        'Name wrong'.$config['name'].'expected, but '.$user->getUsername().' found.');
    }
    if (isset($config['email']))
    {
      Assert::assertEquals($user->getEmail(), $config['email'],
        'E-Mail wrong'.$config['email'].'expected, but '.$user->getEmail().' found.');
    }
    if (isset($config['email']))
    {
      Assert::assertEquals($user->getCountry(), $config['country'],
        'Country wrong'.$config['country'].'expected, but '.$user->getCountry().' found.');
    }
    if (isset($config['token']))
    {
      Assert::assertEquals($user->getUploadToken(), $config['token'], 'Token Invalid');
    }
    if (isset($config['enabled']))
    {
      Assert::assertEquals($user->isEnabled(), 'true' === $config['enabled'], 'Token Invalid');
    }
    if (isset($config['google_uid']))
    {
      Assert::assertEquals($user->getGplusUid(), $config['google_uid'], 'Google UID wrong');
    }
    if (isset($config['google_name']))
    {
      Assert::assertEquals($user->getGplusName(), $config['google_name'], 'Google name wrong');
    }
  }

  public function getDefaultUser(): User
  {
    if (null === UserDataFixtures::$default_user)
    {
      UserDataFixtures::$default_user = $this->insertUser([]);
    }

    return UserDataFixtures::$default_user;
  }

  public function getCurrentUser(): ?User
  {
    return UserDataFixtures::$current_user;
  }

  public function setCurrentUser(?User $current_user): void
  {
    UserDataFixtures::$current_user = $current_user;
  }

  public function setCurrentUserByUsername(string $current_username): void
  {
    /** @var User $current_user */
    $current_user = $this->user_manager->findUserByUsername($current_username);
    UserDataFixtures::$current_user = $current_user;
  }

  public static function clear(): void
  {
    UserDataFixtures::$number_of_users = 0;
    UserDataFixtures::$default_user = null;
    UserDataFixtures::$current_user = null;
  }

  public function createdAt(array $config = []): void
  {
    /** @var User $user */
    $user = $this->user_manager->findUserByUsername($config['name']);
    $date = date_create($config['created_at']) ?? date_create($config['created_at'] ?? 'last Monday');
    $user->changeCreatedAt($date);
    $this->user_manager->updateUser($user, true);
  }
}
