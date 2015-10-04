<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $userName;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $hashedPassword;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return User
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set hashedPassword
     *
     * @param string $hashedPassword
     *
     * @return User
     */
    public function setHashedPassword($hashedPassword)
    {
        $this->hashedPassword = $hashedPassword;

        return $this;
    }

    /**
     * Get hashedPassword
     *
     * @return string
     */
    public function getHashedPassword()
    {
        return $this->hashedPassword;
    }

    /**
     * @return bool
     */
    public function verifyPassword($plainTextPassword)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findByUsername($this->getUserName());

        if (!$user) {
            return false;
        }

        return password_verify($plainTextPassword, $user->getHashedPassword());
    }
}
