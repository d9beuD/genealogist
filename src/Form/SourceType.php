<?php

namespace App\Form;

use App\Entity\Source;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class SourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'form.field.type',
                'choices' => [
                    'form.choice.certificate_birth' => Source::CERT_BIRTH,
                    'form.choice.certificate_baptism' => Source::CERT_BAPTISM,
                    'form.choice.certificate_marriage' => Source::CERT_MARRIAGE,
                    'form.choice.certificate_death' => Source::CERT_DEATH,
                    'form.choice.certificate_military' => Source::CERT_MILITARY,
                    'form.choice.other' => Source::CERT_OTHER,
                ],
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
            ])
            ->add('comment', null, [
                'label' => 'form.field.comment',
                'constraints' => [
                    new Length(['max' => 255]),
                ],
            ])
            ->add('directProof', null, [
                'label' => 'form.field.direct_proof',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Source::class,
        ]);
    }
}
