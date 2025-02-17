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

        $commentaires = $post->getCommentairesPost();

        return $this->render('comments/comments.html.twig', [
            'post' => $post,
            'commentaires' => $commentaires,
        ]);
    }

    #[Route('/ajout/{id}', name: 'commentaire_ajout', methods: ['POST', 'GET'])]
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

}
