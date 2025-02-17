<?php

namespace App\Form;

use App\Entity\CategorieCours;
use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;


class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titreCours')
        ->add('descriptionCours')
        ->add('video', FileType::class, [
            'label'    => 'Vidéo du cours (optionnelle)',
            'mapped'   => false,
            'required' => false, // Allow optional uploads
            'constraints' => [
                new File([
                    'maxSize' => '100M',  // Set the max file size
                    'maxSizeMessage' => 'La taille du fichier est trop grande. Veuillez télécharger une vidéo de moins de 100 Mo.',
                    'mimeTypes' => [
                        'video/mp4',
                        'video/mpeg',
                        'video/quicktime',
                        'video/x-msvideo', // AVI
                        'video/x-matroska', // MKV
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une vidéo valide (MP4, MPEG, MOV, AVI, MKV).'
                ])
            ],
        ])
        
        ->add('imageCours', FileType::class, [
            'label' => 'Image du cours',
            'mapped' => false, // Not mapped to entity directly
            'required' => true, // Ensure it's mandatory
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez télécharger une image',
                    'groups' => ['create']
                ]),
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WebP)',
                ]),
            ],
        ])
        
        
        ->add('categorieC', EntityType::class, [
            'class' => CategorieCours::class,
            'choice_label' => 'nomCategorie', 
            'placeholder' => 'Sélectionnez une catégorie',
            'required' => true
        ])
        ->add('submit', SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => ['class' => 'btn btn-success']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
