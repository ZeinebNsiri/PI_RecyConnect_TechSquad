<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\CategoriArticleType;
use App\Entity\CategorieArticle;
use App\Repository\CategorieArticleRepository;

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
    public function addCategorieArticle(ManagerRegistry $manager, Request $req, CategorieArticleRepository $repository)
    {
        $em= $manager->getManager();

        $categorieArticle= new   CategorieArticle();
        $form = $this ->createForm(CategoriArticleType::class, $categorieArticle);
        $form->handleRequest($req);

        if($form->isSubmitted())
        {
            $em->persist($categorieArticle);
            $em->flush();
            return $this->redirectToRoute('app_addCategorieArticle');
            
        }
        $categories= $repository-> findAll();

        return $this->render('categorie_article/index.html.twig',[
            
            'form'=>$form->createView(),
            'categories' => $categories

        ]);
       
    }

    #[Route('/categorie/article/delete/{id}', name: 'app_deleteCategorieArticle')]
    public function deleteCategorieArticle (ManagerRegistry $manager, CategorieArticleRepository $repository, $id) {
        $em= $manager->getManager();
       
        $categorieArticle = $repository -> find($id);
        $em -> remove($categorieArticle);

        $em -> flush();

        return $this-> redirectToRoute('app_addCategorieArticle'); 
    }

    #[Route('/categorie/article/update/{id}',name:'pp_updateCategorieArticle')]

    public function updateCategorieArticle(Request $req,ManagerRegistry $manager,CategorieArticle $categorieArticle ,CategorieArticleRepository $repo, int $id){
        $em= $manager->getManager();
        $categorieArticle = $repo->find($id);

      $form = $this->createForm(CategoriArticleType::class,$categorieArticle);

      $form->handleRequest($req);

      if($form->isSubmitted())

      {

      $em->flush();

      return $this->redirectToRoute('app_addCategorieArticle');

      }
      return $this->render('categorie_article/index.html.twig',[

        'form'=>$form->createView(),
        'categories' => $categorieArticle

      ]);
    }
}
