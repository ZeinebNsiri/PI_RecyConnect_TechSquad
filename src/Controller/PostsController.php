<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Utilisateur;
use App\Entity\MediaPost;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MediaPostRepository;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\LikeRepository;
use App\Entity\Like;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class PostsController extends AbstractController
{   
    
    #[Route('/posts', name: 'app_posts')]
    public function index(EntityManagerInterface $entityManager, MediaPostRepository $mediaPostRepository, PaginatorInterface $paginator, Request $request): Response
    {   
        if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_PROFESSIONNEL')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }
        $user = $this->getUser();

        $posts = $entityManager->getRepository(Post::class)->findBy(['status_post' => true], ['datePublication' => 'DESC']);
        $postsWithMedia = [];
        foreach ($posts as $post) {
            $medias = $mediaPostRepository->findBy(['post' => $post]);
            $postsWithMedia[] = [
                'post' => $post,
                'medias' => $medias,
                
            ];
        }


        $pagination = $paginator->paginate(
            $postsWithMedia, // Données à paginer
            $request->query->getInt('page', 1), // Numéro de page
            5 // Nombre de posts par page
        );

        $myPostsWithMedia = [];
        foreach ($posts as $post) {
            if ($post->getUserP() === $user) {
                $medias = $mediaPostRepository->findBy(['post' => $post]);
                $myPostsWithMedia[] = [
                    'post' => $post,
                    'medias' => $medias,
                    
                ];
            }
        }

        return $this->render('posts/posts1.html.twig', [
            'postsWithMedia' => $pagination,
            'user' => $user,
            'myPostsWithMedia' => $myPostsWithMedia,
        ]);
    }


    #[Route('/posts/new', name: 'post_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, #[Autowire('%kernel.project_dir%/public/posts/uploads')] string $uploadsDir): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post, ['validation_groups' => ['create']]);
        $form->handleRequest($request);
        

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
            
            if (!$user) {
                $this->addFlash('error', 'Aucun utilisateur connecté');
                return $this->redirectToRoute('app_posts');
            }

            $post->setUserP($user);

            // Gestion des tags
            $tags = $form->get('tags')->getData();
            foreach ($tags as $tagValue) {
                $post->addTag($tagValue); // Méthode à ajouter dans votre entité Post
            }


            // Gestion du fichier média
            $mediaFiles = $form->get('media')->getData();
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

        if ($form->isSubmitted() && !$form->isValid()) {
            // Récupérer les erreurs
            foreach ($form->getErrors() as $error) {
                // Ajouter le message d'erreur
                $this->addFlash('error', $error->getMessage());
            }
        }

        return $this->render('posts/ajoutPost.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/post/like/{id}', name: 'post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $entityManager, LikeRepository $likeRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        
        $existingLike = $likeRepository->findOneBy([
            'post_like' => $post,
            'user_like' => $user
        ]);

        if ($existingLike) {
            
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
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder
            ->select("SUBSTRING(p.datePublication, 6, 2) as month, COUNT(p.id) as count")
            ->from('App\Entity\Post', 'p')
            ->where('p.status_post = true')
            ->groupBy('month')
            ->orderBy('month', 'ASC');

        $stats = $queryBuilder->getQuery()->getResult();

        // Initialisation des données pour tous les mois (1 à 12)
        $monthlyData = array_fill(0, 12, 0);

        // Remplissage des données selon les résultats de la requête
        foreach ($stats as $stat) {
            $monthlyData[intval($stat['month']) - 1] = intval($stat['count']);
        }

        return $this->render('posts/listePosts.html.twig', [
            'posts' => $posts,
            'monthlyData' => json_encode(array_values($monthlyData)), 
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
    public function deleteAdmin(Post $post, EntityManagerInterface $entityManager): Response
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


    #[Route('/post/delete/{id}', name: 'post_delete')]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user || $post->getUserP() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer ce post.');
            return $this->redirectToRoute('app_posts');
        }

     
        $mediaPosts = $entityManager->getRepository(MediaPost::class)->findBy(['post' => $post]);
        foreach ($mediaPosts as $mediaPost) {
            $entityManager->remove($mediaPost);
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $this->addFlash('success', 'Post supprimé avec succès !');
        return $this->redirectToRoute('app_posts');
    }




    




}
