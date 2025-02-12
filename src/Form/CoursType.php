<?php

namespace App\Form;

use App\Entity\CategorieCours;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titreCours')
        ->add('descriptionCours')
        ->add('video')
        ->add('imageCours')
        ->add('categorieC', EntityType::class, [
            'class' => CategorieCours::class,
            'choice_label' => 'nomCategorie', 
            'placeholder' => 'Sélectionnez une catégorie',
            'required' => true
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => ['class' => 'btn btn-primary']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
