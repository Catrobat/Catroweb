<?php

namespace App\System\Testing\DataFixtures;

use App\DB\Entity\User\User;
use App\DB\Generator\MyUuidGenerator;
use App\User\UserManager;
use App\Utils\TimeUtils;
use PHPUnit\Framework\Assert;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

/**
 * Class UserDataFixtures.
 *
 * Use this class in the test environment to easily create new users in the database.
 */
class UserDataFixtures
{
  private static ?User $default_user = null;

  private static ?User $current_user = null;

  private static int $number_of_users = 0;

  public function __construct(
    private readonly UserManager $user_manager,
    private readonly PasswordHasherFactoryInterface $password_hasher_factory
  ) {
  }

  public function insertUser(array $config = [], bool $andFlush = true): User
  {
    if (array_key_exists('id', $config)) {
      // use a fixed ID
      MyUuidGenerator::setNextValue($config['id']);
    }

    ++UserDataFixtures::$number_of_users;

    /** @var User $user */
    $user = $this->user_manager->create();
    $user->setUsername($config['name'] ?? 'User'.UserDataFixtures::$number_of_users);
    $user->setEmail($config['email'] ?? $user->getUsername().'@catrobat.at');
    $user->setPlainPassword($config['password'] ?? '123456');
    $user->setUploadToken($config['token'] ?? 'default_token_'.UserDataFixtures::$number_of_users);
    $user->setSuperAdmin(isset($config['admin']) && 'true' === $config['admin']);
    $user->setEnabled(!isset($config['enabled']) || 'true' === $config['enabled']);
    $user->addRole($config['role'] ?? 'ROLE_USER');
    $user->setOauthUser(isset($config['oauth_user']) && 'true' === $config['oauth_user']);
    $this->user_manager->updateUser($user, $andFlush);

    return $user;
  }

  public function assertUser(array $config = []): void
  {
    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($config['name']);

    Assert::assertNotNull($user);

    if (isset($config['name'])) {
      Assert::assertEquals($user->getUserIdentifier(), $config['name'],
        'Name wrong'.$config['name'].'expected, but '.$user->getUserIdentifier().' found.');
    }
    if (isset($config['email'])) {
      Assert::assertEquals($user->getEmail(), $config['email'],
        'E-Mail wrong'.$config['email'].'expected, but '.$user->getEmail().' found.');
    }
    if (isset($config['token'])) {
      Assert::assertEquals($user->getUploadToken(), $config['token'], 'Token Invalid');
    }
    if (isset($config['enabled'])) {
      Assert::assertEquals($user->isEnabled(), 'true' === $config['enabled'], 'Enabled wrong.');
    }
    if (isset($config['password'])) {
      $hasher = $this->password_hasher_factory->getPasswordHasher($user);
      Assert::assertTrue($hasher->verify($user->getPassword(), $config['password']), 'Password invalid');
    }
  }

  public function getDefaultUser(): User
  {
    if (null === UserDataFixtures::$default_user) {
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

  /**
   * @throws \Exception
   */
  public function overwriteCreatedAt(array $config = []): void
  {
    /** @var User $user */
    $user = $this->user_manager->findUserByUsername($config['name']);
    $date = isset($config['created_at']) ? new \DateTime($config['created_at']) : TimeUtils::getDateTime();
    $user->changeCreatedAt($date);
    $this->user_manager->updateUser($user, true);
  }
}
