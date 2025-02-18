<?php

namespace App\Controller;

use App\Entity\CategorieArticle;
use App\Form\CategoriArticleType;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategorieArticleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class CategorieArticleController extends AbstractController
{
    #[Route('/categorie/article', name: 'app_categorie_article')]
    public function index(): Response
    {
        return $this->render('categorie_article/index.html.twig', [
            'controller_name' => 'CategorieArticleController',
        ]);
    }

    #[Route('/categorie/article/add', name: 'app_addCategorieArticle')]
    public function addCategorieArticle(ManagerRegistry $manager, Request $req, CategorieArticleRepository $repository, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/photo_dir')] string $brochuresDirectory, ArticleRepository $articleRepo,)
    {
        $em= $manager->getManager();

        $categorieArticle= new   CategorieArticle();
        $form = $this ->createForm(CategoriArticleType::class, $categorieArticle, [
            'validation_groups' => ['create']
        ]);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid() )
        {
            $image_categorie = $form->get('image_categorie')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image_categorie) {
                $originalFilename = pathinfo($image_categorie->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image_categorie->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image_categorie->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $categorieArticle->setImageCategorie($newFilename);
            }

            try {
                $em->persist($categorieArticle);
                $em->flush();
                $this->addFlash('success', 'Catégorie article ajoutée avec succès!');
                return $this->redirectToRoute('app_addCategorieArticle');
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('danger', 'Le nom de la catégorie existe déjà!');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'ajout de la catégorie.');
            }
            
        }
        $categories= $repository-> findAll();
        //calcule des nb articles par categorie
        $articleCounts = [];

        foreach ($categories as $category) {
            $articleCounts[$category->getId()] = $articleRepo->countArticlesInCategory($category->getId());
        }


        if ($form->isSubmitted() && !$form->isValid()) {
            // Récupérer les erreurs
            foreach ($form->getErrors() as $error) {
                // Ajouter le message d'erreur
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('categorie_article/index.html.twig',[
            
            'form'=>$form->createView(),
            'categories' => $categories,
            'articleCounts' => $articleCounts,

        ]);
       
    }

    #[Route('/categorie/article/delete/{id}', name: 'app_deleteCategorieArticle')]
    public function deleteCategorieArticle (ManagerRegistry $manager, CategorieArticleRepository $repository, $id) {
        $em= $manager->getManager();
       
        $categorieArticle = $repository -> find($id);
        $em -> remove($categorieArticle);

        $em -> flush();
        $this->addFlash('success', 'Catégorie article supprimée avec succès!');
        return $this-> redirectToRoute('app_addCategorieArticle'); 
    }

    #[Route('/categorie/article/update/{id}',name:'pp_updateCategorieArticle')]

    public function updateCategorieArticle(Request $req,ManagerRegistry $manager,CategorieArticle $categorieArticle ,CategorieArticleRepository $repo, int $id,SluggerInterface $slugger
    , #[Autowire('%kernel.project_dir%/public/uploads/photo_dir')] string $brochuresDirectory ){
        $em= $manager->getManager();
        $categorieArticle = $repo->find($id);

      $form = $this->createForm(CategoriArticleType::class,$categorieArticle , ['validation_groups' => ['update']]);

      $form->handleRequest($req);

      if($form->isSubmitted() && $form->isValid())

      {
        $image_categorie = $form->get('image_categorie')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image_categorie) {
                $originalFilename = pathinfo($image_categorie->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image_categorie->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image_categorie->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $categorieArticle->setImageCategorie($newFilename);
            }

      $em->flush();
      $this->addFlash('success', 'Catégorie article modifiée avec succès!');

      return $this->redirectToRoute('app_addCategorieArticle');

      }
      if ($form->isSubmitted() && !$form->isValid()) {
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
        }
      return $this->render('categorie_article/updateCatArt.html.twig',[

        'form'=>$form->createView(),
        'categories' => $categorieArticle

      ]);
    }
}
