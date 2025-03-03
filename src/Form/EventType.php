<?php
namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;
    
        $builder
            ->add('nomEvent', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'événement est requis.']),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le nom ne peut pas dépasser 255 caractères.']),
                ]
            ])
            ->add('descriptionEvent', TextareaType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est requise.']),
                ]
            ])
            ->add('lieuEvent', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu de l\'événement est requis.']),
                ]
            ])
            ->add('dateEvent', DateType::class, [
                'widget' => 'single_text',
                'empty_data' => null,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de l\'événement est requise.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être future ou aujourd\'hui.',
                    ]),
                ]
            ])
            ->add('heureEvent', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime('now'),
                'constraints' => [
                    new NotBlank(['message' => 'L\'heure de l\'événement est requise.']),
                ],
            ])
            ->add('capacite', IntegerType::class, [
                'empty_data' => 0, // Default to 0 if empty
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La capacité est requise.']),
                    new Assert\Type([
                        'type' => 'integer',
                        'message' => 'Veuillez entrer un nombre entier.', // Custom error message in French
                    ]),
                    new Assert\Positive(['message' => 'La capacité doit être un nombre positif.']),
                ]
            ])
            ->add('imageEvent', FileType::class, [
                'label' => 'Image de l\'événement',
                'mapped' => false,
                'required' => !$isEdit, // Make the field optional in edit mode
                'constraints' => $isEdit ? [] : [
                    new NotBlank(['message' => 'Veuillez télécharger une image.']),
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, PNG, WebP).',
                    ]),
                ],
            ])
            ->add('mapCoordinates', TextType::class, [
                'label' => 'Map Coordinates (Latitude, Longitude)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g., 48.8566, 2.3522',
                ],
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^-?\d+(\.\d+)?,\s*-?\d+(\.\d+)?$/',
                        'message' => 'Please enter valid coordinates in the format "latitude, longitude".',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => $isEdit ? 'Modifier l\'événement' : 'Créer l\'événement',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
            'is_edit' => false, // Default to false for create mode
        ]);
    }
}