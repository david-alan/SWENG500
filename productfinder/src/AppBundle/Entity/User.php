<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 *
 * TODO: inject doctrine entitymanager into constructor
 * rather than extending Controller - quick hack to get
 * access to doctrine
 */
class User extends Controller
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
        var_dump($this->getEmail());
$user = $this->getDoctrine()
    ->getRepository('AppBundle:User')
    ->findOneByEmail($this->getEmail());
        die('x');

        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User');
            //->findByEmail($this->getEmail());
        die('here');
        if (!$user) {
            return false;
        }
var_dump($plainTextPassword);
        var_dump($user->password);
        die();
        return ($plainTextPassword == $user->password);
    }
}
