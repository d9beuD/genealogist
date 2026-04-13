<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembersSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', SearchType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'form.placeholder.search_by_name',
                ],
            ])
            ->add('withoutOwnUnions', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.without_own_unions',
            ])
            ->add('withoutParentUnion', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.without_parent_union',
            ])
            ->setMethod(\Symfony\Component\HttpFoundation\Request::METHOD_GET)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
