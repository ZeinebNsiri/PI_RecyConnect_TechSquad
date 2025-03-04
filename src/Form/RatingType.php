<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\Rating;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RatingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', ChoiceType::class, [
                'choices' => [
                    '5 étoile'  => 5,
                    '4 étoiles' => 4,
                    '3 étoiles' => 3,
                    '2 étoiles' => 2,
                    '1 étoiles' => 1,
                    'Remise à zéro' => 0,
                ],
                // On peut laisser expanded => true (affiche des radios)
                // puis on gère l'aspect « étoiles » via du CSS/JS
                'expanded' => true,
                'multiple' => false,
                // Pour ne pas afficher « Votre note » en label
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Rating::class,
        ]);
    }
}
