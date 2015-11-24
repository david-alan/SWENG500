<?php
namespace AppBundle\Services;

use AppBundle\Controller\DefaultController;
use Doctrine\ORM\EntityManager;
use Appbundle\Entity\User;

class LoginService extends DefaultController
{
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function logUserIn($user, $password)
    {
        $repository = $this->em->getRepository(User::class);
        $userSearch = $repository->findOneByEmail($user->getEmail());

        //check password is valid
        if($this->container->get('password_service')
            ->verifyPassword($userSearch, $password)) {
                return $userSearch->getEmail();
        }
        return false;
    }
}