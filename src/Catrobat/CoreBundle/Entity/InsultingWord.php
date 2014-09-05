<?php


namespace Catrobat\CoreBundle\Entity;

/**
 * @ORM\Entity
 * @ORM\Table(name="badwords")
 * @ORM\Entity(repositoryClass="Catrobat\CoreBundle\Entity\InsultingWordsRepository")
 */
class InsultingWord
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $word;
} 