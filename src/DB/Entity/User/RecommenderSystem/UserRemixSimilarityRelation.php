<?php

declare(strict_types=1);

namespace App\DB\Entity\User\RecommenderSystem;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\RecommenderSystem\UserRemixSimilarityRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_remix_similarity_relation')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserRemixSimilarityRelationRepository::class)]
class UserRemixSimilarityRelation extends AbstractSimilarityRelation
{
  #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY', inversedBy: 'relations_of_similar_users_based_on_remixes')]
  protected User $first_user;

  #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY', inversedBy: 'reverse_relations_of_similar_users_based_on_remixes')]
  protected User $second_user;
}
