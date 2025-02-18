<?php

namespace App\Form;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu_com', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le contenu du post est obligatoire.',
                        'groups' => ['create','update']
                    ]),
                ],
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
