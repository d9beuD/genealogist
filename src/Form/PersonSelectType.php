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
        /** @var Tree */
        $currentTree = $options['current_tree'];
        /** @var Collection<int, Person> */
        $unionMembers = $options['members_to_exclude'];

        $builder
            ->add('person', EntityType::class, [
                'class' => Person::class,
                'choices' => array_diff(
                    $currentTree->getMembers()->toArray(), 
                    $unionMembers->toArray()
                ),
                'label' => 'Conjoint(e)',
                'multiple' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'current_tree' => null,
            'members_to_exclude' => null,
        ]);
    }
}
