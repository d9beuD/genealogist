<?php

namespace App\Form;

use App\Entity\Person;
use App\Entity\Tree;
use App\Repository\PersonRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        $builder
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'choices' => array_diff(
                    $availableMembers, 
                    $unionMembers
                ),
                'label' => 'Conjoint(e)',
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'available_members' => null,
            'union_members' => null,
        ]);
    }
}
