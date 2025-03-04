<?php

namespace App\Controller;

use App\Entity\CategorieCours;
use App\Form\CategorieCoursType;
use App\Repository\CategorieCoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Attribute\Route;

class CategoriecoursController extends AbstractController
{
    


    #[Route('/allcategoriecours', name: 'app_allcategoriecours')]
public function getAllcategoriecours(
    CategorieCoursRepository $repo,
    PaginatorInterface $paginator,
    Request $request
): Response {
    // 1) Construire la requête Doctrine (QueryBuilder ou Query)
    $query = $repo->createQueryBuilder('c')
        ->orderBy('c.id', 'DESC')
        ->getQuery();

    // 2) Paginer le résultat
    //    - $request->query->getInt('page', 1) : numéro de page
    //    - 5 : nombre de catégories par page
    $categories = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        5
    );

    // 3) Renvoyer le template en passant les catégories paginées
    return $this->render('categoriecours/index.html.twig', [
        'categories' => $categories,
    ]);
}
    
            //add
        #[Route('/add-categorie-cours', name: 'categorie_cours_add')]
        public function add(Request $request, ManagerRegistry $manager): Response
        {
            $categorie = new CategorieCours();
            $form = $this->createForm(CategorieCoursType::class, $categorie);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                
                $em = $manager->getManager();
                $em->persist($categorie);
                $em->flush();

                $this->addFlash('success', 'La catégorie a été ajoutée avec succès !');

                return $this->redirectToRoute('app_allcategoriecours');
            }

            return $this->render('categoriecours/add-categorie.html.twig', [
                'form' => $form->createView() 
            ]);
        }

        //edit
                    #[Route('/updatecategorie/{id}', name: 'app_editcategoriecours')]
                       public function updateCategorie(
                      ManagerRegistry $manager, 
                CategorieCoursRepository $repo, 
                Request $req, 
                $id
            ) {
                $categorie = $repo->find($id);
                
                if (!$categorie) {
                    throw $this->createNotFoundException('Catégorie non trouvée');
                }

                $form = $this->createForm(CategorieCoursType::class, $categorie);
                $form->handleRequest($req);

                if ($form->isSubmitted() && $form->isValid()) {
                    $em = $manager->getManager();
                    $em->flush();

                    
                    $this->addFlash('success', 'La catégorie a été modifiée avec succès.');

                    return $this->redirectToRoute('app_allcategoriecours');
                }

                return $this->render('categoriecours/edit-categorie.html.twig', [
                    'form' => $form->createView()
                ]);
            }

    
        //delete
        #[Route('/deletecategorie/{id}', name: 'app_deletecategoriecours')]
        public function deleteCategorie(ManagerRegistry $manager, CategorieCoursRepository $repo, $id )
        {
            $em= $manager->getManager();
            $categorie = $repo->find($id);
          
            $em->remove($categorie)  ;
            $em->flush();
    
            return $this->redirectToRoute('app_allcategoriecours');
        }
    
    
    
    

}
