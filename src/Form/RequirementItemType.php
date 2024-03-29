<?php

namespace App\Form;

use App\Entity\RequirementItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;


class RequirementItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le résumé des prérequis d\'une offre est requise.']),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage'=> 'La description d\'un prérequis doit contenir au moins 2 caractères',
                        'maxMessage'=> 'La description d\'un prérequis doit contenir maximum 255 caractères',
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Prérequis'
                ],
                'label' => ' '
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RequirementItem::class,
        ]);
    }
}
