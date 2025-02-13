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

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomEvent', TextType::class)
            ->add('descriptionEvent', TextareaType::class)
            ->add('lieuEvent', TextType::class)
            ->add('dateEvent', DateType::class, ['widget' => 'single_text'])
            ->add('heureEvent', TimeType::class, ['widget' => 'single_text'])
            ->add('capacite', IntegerType::class)
            ->add('imageEvent', FileType::class, [
                'label' => 'Image de l\'événement',
                'mapped' => false,
                'required' => false, 
                'data_class' => null, 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}