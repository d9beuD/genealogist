<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portrait', FileType::class, [
                'label' => 'Portrait',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
            ->add('firstname')
            ->add('lastname')
            ->add('bio', TextareaType::class, [
                'required' => false,
            ])
            ->add('birth')
            ->add('birthDaySure', CheckboxType::class, [
                'required' => false,
                'label' => 'Jour certain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('birthMonthSure', CheckboxType::class, [
                'required' => false,
                'label' => 'Mois certain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('birthYearSure', CheckboxType::class, [
                'required' => false,
                'label' => 'Année certaine',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('death')
            ->add('deathDaySure', CheckboxType::class, [
                'required' => false,
                'label' => 'Jour certain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('deathMonthSure', CheckboxType::class, [
                'required' => false,
                'label' => 'Mois certain',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('deathYearSure', CheckboxType::class, [
                'required' => false,
                'label' => 'Année certaine',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
