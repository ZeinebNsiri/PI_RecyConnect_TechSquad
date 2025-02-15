<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article1')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/add', name: 'app_addArticle')]
    public function addArticle(ManagerRegistry $manager, Request $req, SluggerInterface $slugger,
    #[Autowire('%kernel.project_dir%/public/uploads/photo_dir')] string $brochuresDirectory, UtilisateurRepository $UtilisateurRepository)
    {
        $em= $manager->getManager();
        $user = $UtilisateurRepository->find(1);
        $Article= new   Article();
        $form = $this ->createForm(ArticleType::class, $Article);
        $form->handleRequest($req);

        if($form->isSubmitted() )
        {
            $image_article = $form->get('image_article')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image_article) {
                $originalFilename = pathinfo($image_article->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image_article->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image_article->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $Article->setImageArticle($newFilename);
            }
            $Article->setUtilisateur($user);
            $em->persist($Article);
            $em->flush();
            $this->addFlash('success', 'L\'article ajouté avec succès!');
            return $this->redirectToRoute('app_article');
            
        }
        

        return $this->render('article/addArticle.html.twig',[
            
            'form'=>$form->createView()

        ]);
       
    }

    #[Route('/Article/getall', name: 'app_article')]
    public function getallArticle(ArticleRepository $repository)
    {
        $articles= $repository-> findAll();
        return $this->render('article/index.html.twig', [
            'articles' => $articles,
        ]);  
    }

    #[Route('/Article/getMine', name: 'app_article_mine')]
    public function getMesArticle(ArticleRepository $repository)
    {
        $articles= $repository-> findAll();
        return $this->render('article/mesArticle.html.twig', [
            'articles' => $articles,
        ]);  
    }

    #[Route('/article/detail/{id}',name:'detail_article')]

    public function DetailsArticle(ArticleRepository $repo, int $id){
        
        $article = $repo->find($id);
        
        return $this->render('article/detailsArticle.html.twig',[
        'article' => $article
      ]);
    }


    #[Route('/article/update/{id}',name:'pp_updateArticle')]

    public function updateArticle(Request $req,ManagerRegistry $manager,Article $Article ,ArticleRepository $repo, int $id,SluggerInterface $slugger
    , #[Autowire('%kernel.project_dir%/public/uploads/photo_dir')] string $brochuresDirectory ){
        $em= $manager->getManager();
        $Article = $repo->find($id);

      $form = $this->createForm(ArticleType::class,$Article);

      $form->handleRequest($req);

      if($form->isSubmitted())

      {
        $image_article = $form->get('image_article')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($image_article) {
                $originalFilename = pathinfo($image_article->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image_article->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image_article->move($brochuresDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $Article->setImageArticle($newFilename);
            }

      $em->flush();
      $this->addFlash('success', 'L\'article est modifié avec succès!');

      return $this->redirectToRoute('app_article_mine');

      }
      return $this->render('article/addArticle.html.twig',[

        'form'=>$form->createView(),
        'articles' => $Article

      ]);
    }

    #[Route('article/delete/{id}', name: 'app_deleteArticle')]
    public function deleteArticle (ManagerRegistry $manager, ArticleRepository $repository, $id) {
        $em= $manager->getManager();
       
        $Article = $repository -> find($id);
        $em -> remove($Article);

        $em -> flush();
        $this->addFlash('success', ' L\'article supprimé avec succès!');
        return $this-> redirectToRoute('app_article_mine'); 
    }
}
