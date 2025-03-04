<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EventType;
use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/events', name: 'events_list')]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $name = $request->query->get('name');
        $location = $request->query->get('location');
        $date = $request->query->get('date');
        $type = $request->query->get('type'); // Ensure this is passed
    
        $events = $evenementRepository->searchEvents($name, $location, $date, $type);
    
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'name' => $name,
            'location' => $location,
            'date' => $date,
            'type' => $type, // Pass the type to the template
        ]);
    
    }

    #[Route('/events/search', name: 'event_search')]
    public function search(Request $request, EvenementRepository $evenementRepository): JsonResponse
    {
        $name = $request->query->get('name');
        $events = $evenementRepository->findByName($name);

        $results = [];
        foreach ($events as $event) {
            $results[] = [
                'id' => $event->getId(),
                'name' => $event->getNomEvent(),
            ];
        }

        return new JsonResponse($results);
    }

    #[Route('/event/{id}', name: 'event_show')]
    public function detail(EvenementRepository $evenementRepository, ReservationRepository $reservationRepository, int $id): Response
    {
        $evenement = $evenementRepository->find($id);

        if (!$evenement) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        $user = $this->getUser();
        $meetingLink = null;
        $isEventActive = false;
        $isUserRegistered = false;

        if ($user) {
            $reservation = $reservationRepository->findOneBy([
                'event' => $evenement,
                'user_id' => $user,
            ]);

            if ($reservation) {
                $isUserRegistered = true;
                $meetingLink = $reservation->getMeetingLink();

                $now = new \DateTime();
                $eventDate = $evenement->getDateEvent();
                $eventStart = new \DateTime($eventDate->format('Y-m-d') . ' ' . $evenement->getHeureEvent()->format('H:i:s'));
                $eventEnd = new \DateTime($eventDate->format('Y-m-d') . ' ' . $evenement->getEndTime()->format('H:i:s'));

                $isEventActive = ($now >= $eventStart && $now <= $eventEnd);
            }
        }

        return $this->render('event/detail.html.twig', [
            'evenement' => $evenement,
            'meetingLink' => $meetingLink,
            'isEventActive' => $isEventActive,
            'isUserRegistered' => $isUserRegistered,
            'mapCoordinates' => $evenement->getMapCoordinates(),
        ]);
    }

    #[Route('/admin/events', name: 'admin_events')]
public function adminIndex(Request $request, EvenementRepository $evenementRepository): Response
{
    $searchTerm = $request->query->get('search', '');
    $location = $request->query->get('location', '');
    $date = $request->query->get('date', '');
    $type = $request->query->get('type', ''); // New type filter

    $events = $evenementRepository->searchEventsAdmin($searchTerm, $location, $date, $type);

    return $this->render('event/admin_events.html.twig', [
        'events' => $events,
        'searchTerm' => $searchTerm,
        'location' => $location,
        'date' => $date,
        'type' => $type, // Pass the type to the template
    ]);
}

    #[Route('/admin/events/create', name: 'create_event', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Evenement();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('imageEvent')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $uploadDir = $this->getParameter('photo_dir');
    
                // Ensure the upload directory exists
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
    
                // Move the uploaded file to the directory
                $imageFile->move($uploadDir, $newFilename);
                $event->setImageEvent($newFilename);
            } else {
                // Set a default image if no file is uploaded
                $event->setImageEvent('uploads/images/default.png');
            }
    
            // Set Google Meet link for online events
            if (strtolower($event->getLieuEvent()) === 'en ligne') {
                $event->setGoogleMeetLink('https://meet.jit.si/' . uniqid());
            }
    
            // Calculate start and end times
            $eventDate = $event->getDateEvent();
            $eventTime = $event->getHeureEvent();
    
            // Format the date and time into a string before creating the DateTime object
            $startTime = new \DateTime($eventDate->format('Y-m-d') . ' ' . $eventTime->format('H:i:s'));
    
            // Clone the start time and add 2 hours for the end time
            $endTime = (clone $startTime)->modify('+2 hours');
            $event->setEndTime($endTime);
    
            // Set the remaining places to the event capacity
            $event->setNbRestant($event->getCapacite());
    
            // Persist and flush the event to the database
            $entityManager->persist($event);
            $entityManager->flush();
    
            // Add a success flash message and redirect
            $this->addFlash('success', 'L\'événement a été créé avec succès.');
            return $this->redirectToRoute('admin_events');
        }
    
        // Render the form template
        return $this->render('event/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/admin/events/edit/{id}', name: 'edit_event', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $event, EntityManagerInterface $entityManager): Response
    {
        $oldImage = $event->getImageEvent();

        $form = $this->createForm(EventType::class, $event, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageEvent')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $uploadDir = $this->getParameter('photo_dir');

                $imageFile->move($uploadDir, $newFilename);
                $event->setImageEvent($newFilename);

                if ($oldImage && file_exists($uploadDir . '/' . $oldImage)) {
                    unlink($uploadDir . '/' . $oldImage);
                }
            } else {
                $event->setImageEvent($oldImage);
            }

            if (strtolower($event->getLieuEvent()) === 'en ligne' && !$event->getGoogleMeetLink()) {
                $event->setGoogleMeetLink('https://meet.jit.si/' . uniqid());
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