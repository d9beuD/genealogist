<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSelectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Person[] */
        $availableMembers = $options['available_members'];
        /** @var Person[] */
        $unionMembers = $options['union_members'];

        // Prepare the choices
        $choices = array_udiff($availableMembers, $unionMembers, function ($a, $b) {
            return $a->getId() <=> $b->getId();
        });
        usort($choices, function ($a, $b) {
            return $a->getFullName() <=> $b->getFullName();
        });

        $builder
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'choices' => $choices,
                'label' => 'Conjoint(e)',
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'available_members' => [],
            'union_members' => [],
        ]);
    }
}
