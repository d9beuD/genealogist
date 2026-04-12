<?php

namespace App\Form;

use App\Entity\Union;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('married', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.married',
                'label_attr' => ['class' => 'checkbox-switch'],
            ])
            ->add('startsAt', DateType::class, [
                'required' => false,
                'label' => 'form.field.union_start_date',
                'widget' => 'single_text',
            ])
            ->add('endsAt', DateType::class, [
                'required' => false,
                'label' => 'form.field.union_end_date',
                'widget' => 'single_text',
            ])
            ->add('place', TextType::class, [
                'required' => false,
                'label' => 'form.field.union_place',
            ])
            ->add('dayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_day',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('monthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_month',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('yearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_year',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('endDayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_day',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('endMonthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_month',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('endYearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_year',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch'
                ],
                'row_attr' => [
                    'class' => 'd-inline'
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'form.field.description',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Union::class,
        ]);
    }
}
