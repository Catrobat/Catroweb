<?php
/**
 * Created by PhpStorm.
 * User: eric
 * Date: 01.03.16
 * Time: 17:32
 */

namespace Catrobat\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="UserCommentRepository")
 * @ORM\Table(name="user_comment")
 */
class UserComment
{
    /**
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getProgramId()
    {
        return $this->programId;
    }

    /**
     * @param mixed $programId
     */
    public function setProgramId($programId)
    {
        $this->programId = $programId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUploadDate()
    {
        return $this->uploadDate;
    }

    /**
     * @param mixed $uploadDate
     */
    public function setUploadDate($uploadDate)
    {
        $this->uploadDate = $uploadDate;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $programId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @ORM\Column(type="date")
     */
    protected $uploadDate;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

  /**
   * @ORM\Column(type="string")
   */
    protected $username;

}