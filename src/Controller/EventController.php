<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EventType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/events', name: 'events_list')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Evenement::class)->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/event/{id}', name: 'event_show')]
    public function detail(EvenementRepository $evenementRepository, int $id): Response
    {
        $evenement = $evenementRepository->find($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        return $this->render('event/detail.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/admin/events', name: 'admin_events')]
    public function adminIndex(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Evenement::class)->findAll();

        return $this->render('event/admin_events.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/admin/events/create', name: 'create_event', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Evenement();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageEvent')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $uploadDir = $this->getParameter('images_directory');

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $imageFile->move($uploadDir, $newFilename);
                $event->setImageEvent($newFilename);
            }

            $event->setNbRestant($event->getCapacite());

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été créé avec succès.');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/events/edit/{id}', name: 'edit_event', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $oldImage = $event->getImageEvent();

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageEvent')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $uploadDir = $this->getParameter('images_directory');

                $imageFile->move($uploadDir, $newFilename);
                $event->setImageEvent($newFilename);

                if ($oldImage && file_exists($uploadDir . '/' . $oldImage)) {
                    unlink($uploadDir . '/' . $oldImage);
                }
            } else {
                $event->setImageEvent($oldImage);
            }

            $entityManager->flush();

            $this->addFlash('success', 'L\'événement a été mis à jour avec succès.');
            return $this->redirectToRoute('admin_events');
        }

        return $this->render('event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/admin/events/delete/{id}', name: 'delete_event_confirm', methods: ['GET'])]
    public function deleteConfirmEvent(Evenement $event): Response
    {
        return $this->render('event/delete_confirm.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/admin/events/delete/{id}/confirm', name: 'delete_event', methods: ['POST'])]
    public function deleteEvent(Request $request, Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $event->getId(), $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_events');
        }

        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'L\'événement a été supprimé avec succès.');
        return $this->redirectToRoute('admin_events');
    }
}