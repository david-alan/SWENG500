<?php

namespace AppBundle\Form\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email','email')
            ->add('password','password')
            ->add('login','submit',array('label' => 'Login'));

    }

    public function getName()
    {
        return 'login';
    }
}