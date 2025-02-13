<?php
// src/Form/EventType.php

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
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Image;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEvent', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom de l\'événement est obligatoire.']),
                    new Length(['min' => 3, 'max' => 255, 'minMessage' => 'Le nom de l\'événement doit comporter au moins {{ limit }} caractères.', 'maxMessage' => 'Le nom de l\'événement ne peut pas dépasser {{ limit }} caractères.'])
                ]
            ])
            ->add('descriptionEvent', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description de l\'événement est obligatoire.'])
                ]
            ])
            ->add('lieuEvent', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le lieu de l\'événement est obligatoire.'])
                ]
            ])
            ->add('dateEvent', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de l\'événement est obligatoire.']),
                    new \Symfony\Component\Validator\Constraints\Date(['message' => 'La date doit être au format valide.'])
                ]
            ])
            ->add('heureEvent', TimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'L\'heure de l\'événement est obligatoire.']),
                    new \Symfony\Component\Validator\Constraints\Time(['message' => 'L\'heure doit être au format valide.'])
                ]
            ])
            ->add('capacite', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La capacité est obligatoire.']),
                    new GreaterThan(['value' => 0, 'message' => 'La capacité doit être supérieure à 0.'])
                ]
            ])
            ->add('imageEvent', FileType::class, [
                'label' => 'Image de l\'événement',
                'mapped' => false,
                'required' => true,
            
                
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
