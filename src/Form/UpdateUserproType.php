<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UpdateUserproType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('photo_profil', FileType::class, [
            'label' => 'Inserez une image de profile (des images uniquement)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                    ],
                    'mimeTypesMessage' => 'Please upload a valid Image',
                ])
            ],
        ])

        ->add('nom_user', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer votre nom']),
            ],
        ])
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer votre email']),
                new Email(['message' => 'Veuillez entrer une adresse email valide.']),
            ],
        ])
        ->add('numTel', TelType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone']),
                new Regex([
                    'pattern' => '/^[0-9]{8}$/',
                    'message' => 'Le numéro de téléphone doit contenir exactement 8 chiffres.',
                ]),
            ],
        ])
        ->add('adresse')
        ->add('submit', SubmitType::class,['label' => "Enregistrer"]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
