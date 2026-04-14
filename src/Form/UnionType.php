<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\Union;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                    'class' => 'checkbox-inline checkbox-switch',
                ],
                'row_attr' => [
                    'class' => 'd-inline',
                ],
            ])
            ->add('monthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_month',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
                'row_attr' => [
                    'class' => 'd-inline',
                ],
            ])
            ->add('yearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_year',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
                'row_attr' => [
                    'class' => 'd-inline',
                ],
            ])
            ->add('endDayUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_day',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
                'row_attr' => [
                    'class' => 'd-inline',
                ],
            ])
            ->add('endMonthUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_month',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
                'row_attr' => [
                    'class' => 'd-inline',
                ],
            ])
            ->add('endYearUnsure', CheckboxType::class, [
                'required' => false,
                'label' => 'form.field.uncertain_year',
                'label_attr' => [
                    'class' => 'checkbox-inline checkbox-switch',
                ],
                'row_attr' => [
                    'class' => 'd-inline',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'form.field.description',
            ])
        ;

        if (!$options['include_members']) {
            return;
        }

        $builder
            ->add('member1', EntityType::class, [
                'mapped' => false,
                'required' => false,
                'placeholder' => '',
                'class' => Person::class,
                'query_builder' => function (EntityRepository $entityRepository) use ($options): QueryBuilder {
                    $qb = $entityRepository->createQueryBuilder('p');
                    return $qb
                        ->where('p.tree = :tree')
                        ->setParameter('tree', $options['tree']);
                }
            ])
            ->add('member2', EntityType::class, [
                'mapped' => false,
                'required' => false,
                'placeholder' => '',
                'class' => Person::class,
                'query_builder' => function (EntityRepository $entityRepository) use ($options): QueryBuilder {
                    $qb = $entityRepository->createQueryBuilder('p');
                    return $qb
                        ->where('p.tree = :tree')
                        ->setParameter('tree', $options['tree']);
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Union::class,
            'tree' => null,
            'include_members' => false,
        ]);

        $resolver->setAllowedTypes('tree', ['null', Tree::class]);
        $resolver->setAllowedTypes('include_members', 'bool');
    }
}
