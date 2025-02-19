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
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEvent', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom de l\'événement est requis.',
                'groups' => ['create']]),
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le nom ne peut pas dépasser 255 caractères.']),
                ]
            ])
            ->add('descriptionEvent', TextareaType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est requise.',
                'groups' => ['create']]),
                ]
            ])
            ->add('lieuEvent', TextType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le lieu de l\'événement est requis.',
                'groups' => ['create']]),
                ]
            ])
            ->add('dateEvent', DateType::class, [
                'widget' => 'single_text',
                'empty_data' => null,  // Set this to null to allow empty values
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de l\'événement est requise.']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date doit être future ou aujourd\'hui.',
                        'groups' => ['create']
                    ]),
                ]
            ])   
            ->add('heureEvent', TimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'data' => new \DateTime('now'), // Définit une heure par défaut
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'heure de l\'événement est requise.',
                        'groups' => ['create']
                    ]),
                ],
            ])
            
            ->add('capacite', IntegerType::class, [
                'empty_data' => '',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La capacité est requise.',
                    'groups' => ['create']
                ]),
                    new Assert\Positive(['message' => 'La capacité doit être un nombre positif.']),
                ]
            ])
            ->add('imageEvent', FileType::class,[      
                'label' => 'Image de l\'événement',
                'mapped' => false,
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new  NotBlank([
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
        
            ->add('submit', SubmitType::class, [
                'label' => 'Créer l\'événement',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
        }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
    
}
