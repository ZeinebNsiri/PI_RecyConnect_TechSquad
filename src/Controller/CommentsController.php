<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Post;
use App\Entity\Utilisateur;
use App\Form\CommentaireType;
use App\Repository\PostRepository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommentsController extends AbstractController
{
    #[Route('/comments/{id}', name: 'app_comments')]
    public function index(int $id, EntityManagerInterface $entityManager): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post non trouvé.');
        }

        $commentaires = $entityManager->getRepository(Commentaire::class)->findBy([
            'post_com' => $post,
            'parent' => null,  
        ]);
      

        return $this->render('comments/comments.html.twig', [
            'post' => $post,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/comment/ajout/{id}', name: 'commentaire_ajout', methods: ['POST', 'GET'])]
    public function ajouter(
            Request $request,
            EntityManagerInterface $entityManager,
            int $id,
            PostRepository $postRepository
        ): Response {
            $post = $postRepository->find($id); // Récupère le post associé
            if (!$post) {
                throw $this->createNotFoundException('Post non trouvé.');
            }
            $commentaire = new Commentaire();
            $commentaire->setContenuCom($request->request->get('contenu_com'));
            $commentaire->setDateCom(new \DateTime());
            $commentaire->setPostCom($post);

           
            $user = $entityManager->getRepository(Utilisateur::class)->findOneBy([]);
            
            $commentaire->setUserCom($user);

            $entityManager->persist($commentaire);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Commentaire ajouté avec succès.',
            ]);
            

           
            }


    #[Route('/comment/{id}/reply', name: 'comment_reply', methods: ['POST'])]
    public function reply(Request $request, Commentaire $comment, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy([]);
        if (!$user) {
            return $this->redirectToRoute('app_base_front_office');
        }
    
        $contenu = $request->request->get('contenu_com');
    
        if ($contenu) {
            $reply = new Commentaire();
            $reply->setContenuCom($contenu);
            $reply->setUserCom($user);
            $reply->setDateCom(new \DateTime());
            $reply->setPostCom($comment->getPostCom()); 
            $reply->setParent($comment);
    
            $entityManager->persist($reply);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_comments', ['id' => $comment->getPostCom()->getId()]);
    }
        

}
