<?php

namespace App\Form;

use App\Entity\Requirement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Symfony\Component\Validator\Constraints as Assert;
class RequirementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le résumé des prérequis d\'une offre est requise.']),
                    new Assert\Length([
                        'min' => 5,
                        'minMessage'=> 'Le résumé des prérequis d\'une offre doit avoir au moins 5 caractères',
                    ]),
                ]
            ])
            ->add('requirementItems', CollectionType::class, [
                'entry_type' => RequirementItemType::class,
                'label' => 'Details des prérequis',
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => true,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Requirement::class,
        ]);
    }
}
