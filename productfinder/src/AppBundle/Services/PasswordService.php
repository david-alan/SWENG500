<?php
namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Controller\DefaultController;

class PasswordService extends DefaultController
{
    public function verifyPassword(User $user, $plaintextPassword)
    {
        $cypherPassword = $user->getPassword();
        return password_verify($plaintextPassword, $cypherPassword);
    }

    public function hashPassword($plaintextPassword)
    {
        return password_hash($plaintextPassword,PASSWORD_BCRYPT);
    }
}