<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfessionnelRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom_user', TextType::class, [
            'constraints' => [new NotBlank(['message' => 'Veuillez entrer votre nom'])],
        ])
        ->add('matricule_fiscale', TextType::class, [
            'constraints' => [new NotBlank(['message' => 'Veuillez entrer votre matricule fiscale'])],
        ])
        ->add('numTel', TelType::class, [
            'constraints' => [new NotBlank(['message' => 'Veuillez entrer votre numéro de téléphone'])],
        ])
        ->add('email', EmailType::class, [
            'constraints' => [new NotBlank(['message' => 'Veuillez entrer votre email'])],
        ])
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Confirmer le mot de passe'],
            'invalid_message' => 'Les mots de passe ne correspondent pas.',
        ])
        ->add('submit', SubmitType::class, ['label' => "S'inscrire"]);

        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
