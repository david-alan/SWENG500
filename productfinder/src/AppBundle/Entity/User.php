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
     * @ORM\Column(type="string", length=100, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $password;

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
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set hashedPassword
     *
     * @param string $hashedPassword
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get hashedPassword
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getPlaintextPassword()
    {
        return $this->plaintextPassword;
    }

    public function setPlaintextPassword($password)
    {
        $this->plaintextPassword = $password;
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
