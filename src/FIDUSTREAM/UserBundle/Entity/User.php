<?php

namespace FIDUSTREAM\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="FIDUSTREAM\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FIDUSTREAM\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $moderator;
    
   /**
     * @var integer
     *
     * @ORM\Column(name="authentication_level", type="integer", nullable=false)
     */
    protected $authenticationLevel;
    /**
     * Set moderator
     *
     * @param \FIDUSTREAM\UserBundle\Entity\User $moderator
     *
     * @return User
     */
    public function setModerator(\FIDUSTREAM\UserBundle\Entity\User $moderator = null)
    {
        $this->moderator = $moderator;

        return $this;
    }

    /**
     * Get moderator
     *
     * @return \FIDUSTREAM\UserBundle\Entity\User
     */
    public function getModerator()
    {
        return $this->moderator;
    }

    /**
     * Set authenticationLevel
     *
     * @param integer $authenticationLevel
     *
     * @return User
     */
    public function setAuthenticationLevel($authenticationLevel)
    {
        $this->authenticationLevel = $authenticationLevel;

        return $this;
    }

    /**
     * Get authenticationLevel
     *
     * @return integer
     */
    public function getAuthenticationLevel()
    {
        return $this->authenticationLevel;
    }
}
