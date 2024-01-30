<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email.'
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password.'
                    ]),
                ],
            ])
            ->add('remember_me', CheckboxType::class, [
                'label' => 'Se souvenir de moi',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'authenticate',
        ]);
    }
}
