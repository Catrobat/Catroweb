<?php

namespace App\User;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\UserRepository;
use App\Project\ProjectManager;
use App\Security\PasswordGenerator;
use App\Utils\CanonicalFieldsUpdater;
use App\Utils\TimeUtils;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @psalm-suppress InvalidExtendClass
 */
class UserManager implements UserManagerInterface
{
  public function __construct(protected CanonicalFieldsUpdater $canonicalFieldsUpdater, protected UserPasswordHasherInterface $userPasswordHasher, protected EntityManagerInterface $entity_manager, protected TransformedFinder $user_finder, protected ProjectManager $project_manager, protected UrlHelper $url_helper, protected UserRepository $user_repository)
  {
  }

  public function decodeToken(string $token): array
  {
    try {
      $tokenParts = explode('.', $token);
      $tokenPayload = base64_decode($tokenParts[1], true);

      $payload = json_decode($tokenPayload, true, 512, JSON_THROW_ON_ERROR);
      if (!is_array($payload)) {
        return [];
      }

      return json_decode($tokenPayload, true, 512, JSON_THROW_ON_ERROR);
    } catch (\Exception) {
      return [];
    }
  }

  public function isPasswordValid(UserInterface $user, string $password): bool
  {
    return $this->userPasswordHasher->isPasswordValid($user, $password);
  }

  public function getMappedUserData(array $raw_user_data): array
  {
    $response_data = [];

    foreach ($raw_user_data as $user) {
      $response_data[] = [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
        'project_count' => $this->project_manager->countPublicUserProjects($user->getId()),
        'profile' => $user,
      ];
    }

    return $response_data;
  }

  public function updateUser(UserInterface $user, bool $andFlush = true): void
  {
    $this->updatePassword($user);
    $this->entity_manager->persist($user);
    if ($andFlush) {
      $this->entity_manager->flush();
    }
  }

  /**
   * @throws \Exception
   */
  public function createUserFromScratch(array $userdata): ?User
  {
    $scratch_user_id = intval($userdata['id']);
    /** @var User|null $user */
    $user = $this->findOneBy(['scratch_user_id' => $scratch_user_id]);

    if (null === $user) {
      $username = $userdata['username'];
      $user = new User();
      $user->setScratchUserId($scratch_user_id);
      $user->setScratchUsername($username);
      $user->setEmail($username.'@localhost');
      $user->setPlainPassword(PasswordGenerator::generateRandomPassword());
      if ($avatar = $userdata['profile']['images']['90x90'] ?? null) {
        $user->setAvatar($avatar);
      }
      $joined = TimeUtils::dateTimeFromScratch($userdata['history']['joined']);
      if ($joined) {
        $user->changeCreatedAt($joined);
      }
      $this->save($user);
      $this->entity_manager->refresh($user);
    }

    return $user;
  }

  public function search(string $query, ?int $limit = 10, int $offset = 0): array
  {
    $project_query = $this->userSearchQuery($query);

    return $this->user_finder->find($project_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query): int
  {
    $project_query = $this->userSearchQuery($query);

    $paginator = $this->user_finder->findPaginated($project_query);

    return $paginator->getNbResults();
  }

  public function getUserIDList(): array
  {
    $associative_array = $this->entity_manager->createQueryBuilder()
      ->select('user.id as id')
      ->from(User::class, 'user')
      ->getQuery()
      ->execute()
    ;

    return array_map(fn ($value) => $value['id'], $associative_array);
  }

  public function getActiveUserIDList(int $years): array
  {
    $result = $this->entity_manager->createQueryBuilder()
      ->select('user.id as id')
      ->from(User::class, 'user')
      ->leftjoin(\App\DB\Entity\Project\Project::class, 'project', Join::WITH, 'user.id = project.user')
      ->where('user.createdAt <= :date')
      ->setParameter('date', new \DateTime("-{$years} years"))
      ->groupBy('user.id')
      ->having("COUNT(user.id) >= {$years}")
      ->getQuery()
      ->execute()
    ;

    return array_map(fn ($value) => $value['id'], $result);
  }

  protected function userSearchQuery(string $query): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word) {
      $word .= '*';
    }
    unset($word);
    $query = implode(' ', $words);

    $query_string = new QueryString();
    $query_string->setQuery($query);
    $query_string->setFields(['id', 'username']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $bool_query = new BoolQuery();
    $bool_query->addMust($query_string);

    return $bool_query;
  }

  // Sonata ->

  public function updatePassword(UserInterface $user): void
  {
    $plainPassword = $user->getPlainPassword();

    if (null === $plainPassword) {
      return;
    }

    $password = $this->userPasswordHasher->hashPassword($user, $plainPassword);

    $user->setPassword($password);
    $user->eraseCredentials();
  }

  public function findUserByUsername(string $username): ?UserInterface
  {
    return $this->findOneBy([
      'usernameCanonical' => $this->canonicalFieldsUpdater->canonicalizeUsername($username),
    ]);
  }

  public function findUserByEmail(string $email): ?UserInterface
  {
    return $this->findOneBy([
      'emailCanonical' => $this->canonicalFieldsUpdater->canonicalizeEmail($email),
    ]);
  }

  public function findUserByUsernameOrEmail(string $usernameOrEmail): ?UserInterface
  {
    if (1 === preg_match('/^.+@\S+\.\S+$/', $usernameOrEmail)) {
      $user = $this->findUserByEmail($usernameOrEmail);
      if (null !== $user) {
        return $user;
      }
    }

    return $this->findUserByUsername($usernameOrEmail);
  }

  public function findUserByConfirmationToken(string $token): ?UserInterface
  {
    return $this->findOneBy(['confirmation_token' => $token]);
  }

  public function getClass(): string
  {
    return User::class;
  }

  /**
   * @return array<User>
   */
  public function findAll(): array
  {
    return $this->user_repository->findAll();
  }

  public function findBy(array $criteria, array $orderBy = null, int $limit = null, int $offset = null): array
  {
    return $this->user_repository->findBy($criteria, $orderBy, $limit, $offset);
  }

  public function findOneBy(array $criteria, array $orderBy = null): ?User
  {
    return $this->user_repository->findOneBy($criteria, $orderBy);
  }

  public function find($id): ?User
  {
    return $this->user_repository->find($id);
  }

  public function create(): User
  {
    return new User();
  }

  public function save($entity, $andFlush = true): void
  {
    $this->entity_manager->persist($entity);
    if ($andFlush) {
      $this->entity_manager->flush();
    }
  }

  public function delete($entity, $andFlush = true): void
  {
    $this->entity_manager->remove($entity);
    if ($andFlush) {
      $this->entity_manager->flush();
    }
  }

  public function getConnection(): Connection
  {
    return $this->entity_manager->getConnection();
  }
}
