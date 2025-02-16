<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Utilisateur;
use App\Entity\MediaPost;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaPostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\LikeRepository;
use App\Entity\Like;


final class PostsController extends AbstractController
{
    #[Route('/posts', name: 'app_posts')]
    public function index(EntityManagerInterface $entityManager, MediaPostRepository $mediaPostRepository): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy([]);

        $posts = $entityManager->getRepository(Post::class)->findBy(['status_post' => true], ['datePublication' => 'DESC']);
        $postsWithMedia = [];
        foreach ($posts as $post) {
            $medias = $mediaPostRepository->findBy(['post' => $post]);
            $postsWithMedia[] = [
                'post' => $post,
                'medias' => $medias,
                
            ];
        }

        return $this->render('posts/posts1.html.twig', [
            'postsWithMedia' => $postsWithMedia,
            'user' => $user,
        ]);
    }


    #[Route('/posts/new', name: 'post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%/public/posts/uploads')] string $uploadsDir): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $entityManager->getRepository(Utilisateur::class)->findOneBy([]);
            
            if (!$user) {
                $this->addFlash('error', 'Aucun utilisateur trouvé dans la base. Ajoutez un utilisateur avant de tester.');
                return $this->redirectToRoute('app_posts');
            }

            $post->setUserP($user);


            // Gestion du fichier média
            $mediaFiles = $form->get('media')->getData(); // Supposons que "media" est un champ de type file multiple
            dump($mediaFiles);
            foreach ($mediaFiles as $mediaFile) {
                $newFilename = uniqid() . '.' . $mediaFile->guessExtension();
                try {
                    $mediaFile->move(
                        $uploadsDir,
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
                    continue;
                }

                $mediaPost = new MediaPost();
                $mediaPost->setChemin($newFilename);
                $mediaPost->setPost($post);
                $entityManager->persist($mediaPost);
               
            }

            try {
                $entityManager->persist($post);
                $entityManager->flush();
            } catch (\Exception $e) {
                dump($e->getMessage()); // Affiche l'erreur
            }

            $this->addFlash('success', 'Post ajouté avec succès !');
            return $this->redirectToRoute('app_posts');
        }

        return $this->render('posts/ajoutPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/post/like/{id}', name: 'post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $entityManager, LikeRepository $likeRepository): JsonResponse
    {
        $user = $entityManager->getRepository(Utilisateur::class)->findOneBy([]);

        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        // Vérifier si le like existe déjà
        $existingLike = $likeRepository->findOneBy([
            'post_like' => $post,
            'user_like' => $user
        ]);

        if ($existingLike) {
            // Supprimer le like
            $entityManager->remove($existingLike);
            $liked = false;
        } else {
            // Ajouter un like
            $like = new Like();
            $like->setPostLike($post);
            $like->setUserLike($user);
            $entityManager->persist($like);
            $liked = true;
        }

        $entityManager->flush();

        return new JsonResponse([
            'liked' => $liked,
            'likesCount' => count($post->getLikesPost())
        ]);
    }


    #[Route('/admin/posts', name: 'admin_posts')]
    public function listPosts(EntityManagerInterface $entityManager): Response
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();

        return $this->render('posts/listePosts.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/admin/approvePost/{id}', name: 'admin_post_approve')]
    public function approve(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->setStatusPost(true);
        $entityManager->flush();
        return $this->redirectToRoute('admin_posts');
    }

    #[Route('/admin/rejectPost/{id}', name: 'admin_post_reject')]
    public function reject(Post $post, EntityManagerInterface $entityManager): Response
    {
        $post->setStatusPost(false);
        $entityManager->flush();
        return $this->redirectToRoute('admin_posts');
    }

    #[Route('/admin/deletePost/{id}', name: 'admin_post_delete')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        // Supprimer les fichiers médias associés
        $mediaPosts = $entityManager->getRepository(MediaPost::class)->findBy(['post' => $post]);
        foreach ($mediaPosts as $mediaPost) {
            $entityManager->remove($mediaPost);
        }

        $entityManager->remove($post);
        $entityManager->flush();
        return $this->redirectToRoute('admin_posts');
    }


}
