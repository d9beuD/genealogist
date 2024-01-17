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
                'label' => 'Mariés',
                'label_attr' => ['class' => 'checkbox-switch'],
            ])
            ->add('weddingDate', DateType::class, [
                'required' => false,
                'label' => 'Date union',
                'widget' => 'single_text',
            ])
            ->add('weddingPlace', TextType::class, [
                'required' => false,
                'label' => 'Lieu union',
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
