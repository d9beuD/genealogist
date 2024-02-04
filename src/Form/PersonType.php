<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portrait', FileType::class, [
                'label' => 'form.field.portrait',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'form.field.firstname',
                'required' => false,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'form.field.lastname',
                'required' => false,
            ])
            ->add('birthName', TextType::class, [
                'label' => 'form.field.birth_name',
                'required' => false,
            ])
            ->add('otherNames', TextType::class, [
                'label' => 'form.field.other_names',
                'required' => false,
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'form.field.bio',
                'required' => false,
            ])
            ->add('birth', DateType::class, [
                'label' => 'form.field.birth_date',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'form.field.birth_place',
                'required' => false,
            ])
            ->add('birthDayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_day',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('birthMonthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_month',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('birthYearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_year',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('death', DateType::class, [
                'label' => 'form.field.death_date',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('deathPlace', TextType::class, [
                'label' => 'form.field.death_place',
                'required' => false,
            ])
            ->add('deathDayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_day',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('deathMonthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_month',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('deathYearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_year',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('dead', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.deceased',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'form.field.gender',
                'choices' => [
                    'form.choice.male' => Person::MALE,
                    'form.choice.female' => Person::FEMALE,
                    'form.choice.other' => Person::OTHER,
                ],
                'expanded' => true,
                'label_attr' => [
                    'class' => 'radio-inline'
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
