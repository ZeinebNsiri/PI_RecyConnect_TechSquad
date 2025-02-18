<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('contenu', TextareaType::class, [
            'label' => 'Contenu du post',
            'required' => true, // Champ obligatoire
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le contenu du post est obligatoire.',
                        'groups' => ['create','update']
                    ]),
                ],
            
        ])
        ->add('media', FileType::class,  [
            'label' => 'Média (images)',
            'multiple' => true,
            'mapped' => false,
            'required' => false,
            'constraints' => [
                
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, GIF).',
                    
                ]),
            ],
            
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }


    
}
