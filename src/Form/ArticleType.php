<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Utilisateur;
use App\Entity\CategorieArticle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_article', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de l\'article est obligatoire',
                        'groups' => ['create']
                    ])
                ]
            ])
            ->add('description_article', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description de l\'article est obligatoire',
                        'groups' => ['create']
                    ])
                ]
            ])
            ->add('quantite_article', IntegerType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La quantité de l\'article est obligatoire',
                        'groups' => ['create']
                    ]),
                    new Range([
                        'min' => 1,
                        'notInRangeMessage' => 'La quantité ne peut pas être un nombre négatif ou 0',
                        'groups' => ['create']
                    ])
                ]
            ])
            ->add('prix', NumberType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le prix de l\'article est obligatoire',
                        'groups' => ['create']
                    ]),
                    new Range([
                        'min' => 0,
                        'notInRangeMessage' => 'La quantité ne peut pas être un nombre négatif',
                        'groups' => ['create']
                    ])
                ]
            ])
            ->add('image_article', FileType::class, [
                'label' => 'Inserez une image pour l\'article (des images uniquement)',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using attributes
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez télécharger une image',
                        'groups' => ['create']

                    ]),
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ])
                ],
            ])
            ->add('localisation_article', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La localisation de l\'article est obligatoire',
                        'groups' => ['create']
                    ])
                ]
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieArticle::class,
                'choice_label' => 'nomCategorie',
            ])
            ->add('Confirmer', SubmitType::class)
            ->add('Annuler', ResetType::class)
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
