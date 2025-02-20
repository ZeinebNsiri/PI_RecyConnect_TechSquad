<?php

namespace App\Form;
use App\Entity\CategorieArticle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class CategoriArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_categorie', TextType::class, [
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom de la catégorie est obligatoire',
                        'groups' => ['create', 'update']
                    ])
                ]
            ])
            ->add('description_categorie', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'empty_data' => '',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La description de la catégorie est obligatoire!',
                        'groups' => ['create', 'update']
                    ])
                ]
            ])
            
            ->add('image_categorie', FileType::class, [
                'label' => 'Inserey une image pour la catégorie (des images uniquement)',

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
            ->add('Confirmer', SubmitType::class)
            ->add('Annuler', ResetType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategorieArticle::class,
        ]);
    }
}
