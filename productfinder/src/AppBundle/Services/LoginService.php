<?php
namespace AppBundle\Services;

use AppBundle\Controller\DefaultController;
use Doctrine\ORM\EntityManager;
use Appbundle\Entity\User;

class LoginService extends DefaultController
{
    private $em;

    public function __construct(EntityManager $entityManager, $passwordService)
    {
        $this->em = $entityManager;
        $this->passwordService = $passwordService;
    }

    public function logUserIn(User $user, $password)
    {
        $repository = $this->em->getRepository(User::class);
        $userSearch = $repository->findOneByEmail($user->getEmail());

        //check password is valid
        if($userSearch != null && $this->passwordService->verifyPassword($userSearch, $password)) {
                return $userSearch->getEmail();
        }
        return false;
    }

    public function createAccountAndLogUserIn(User $user)
    {
        //salt & hash the password on new accounts
        $cypherPassword = $this->passwordService->hashPassword($user->getPassword());
        $user->setPassword($cypherPassword);

        $this->em->persist($user);
        $this->em->flush();
        return $user->getEmail();
    }
}