<?php

namespace App\Form;

use App\Entity\Offer;
use App\Entity\Candidate;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CandidateAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class)
            ->add('email', EmailType::class)
            ->add('telephone', IntegerType::class)
            ->add('description', TextareaType::class)
            ->add('imageFile', VichFileType::class, [
                'required' => true,
                'download_label' => 'Télécharger',
            ])
            ->add('offer', EntityType::class, [
                'class' => Offer::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
                'group_by' => function (Offer $offer) {
                    return $offer->getCompany();
                },
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidate::class,
        ]);
    }
}
