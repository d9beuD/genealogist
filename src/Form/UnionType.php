<?php

namespace App\Form;

use App\Entity\Union;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
            ->add('weddingDate', DateType::class, [
                'required' => false,
                'label' => 'form.field.union_date',
                'widget' => 'single_text',
            ])
            ->add('weddingPlace', TextType::class, [
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Union::class,
        ]);
    }
}
