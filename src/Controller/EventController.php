<?php
namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EventType;
use App\Repository\EvenementRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function strtolower;
use function uniqid;
use function file_exists;
use function mkdir;
use function unlink;
class EventController extends AbstractController
{
    #[Route('/events', name: 'events_list')]
    public function index(Request $request, EvenementRepository $evenementRepository): Response
    {
        $location = $request->query->get('location');
        $date = $request->query->get('date');

        $events = $evenementRepository->searchEvents($location, $date);

        $onlineEvents = [];
        $onsiteEvents = [];

        foreach ($events as $event) {
            if (strtolower($event->getLieuEvent()) === 'en ligne') {
                $onlineEvents[] = $event;
            } else {
                $onsiteEvents[] = $event;
            }
        }

        return $this->render('event/index.html.twig', [
            'onlineEvents' => $onlineEvents,
            'onsiteEvents' => $onsiteEvents,
            'location' => $location,
            'date' => $date,
        ]);
    }
    #[Route('/event/{id}', name: 'event_show')]
    public function detail(EvenementRepository $evenementRepository, ReservationRepository $reservationRepository, int $id): Response
    {
        $evenement = $evenementRepository->find($id);
    
        if (!$evenement) {
            throw $this->createNotFoundException('Événement non trouvé');
        }
    
        // Check if the current user has registered for this event
        $user = $this->getUser();
        $meetingLink = null;
        $isEventActive = false;
    
        if ($user) {
            $reservation = $reservationRepository->findOneBy([
                'event' => $evenement,
                'user_id' => $user,
            ]);
    
            if ($reservation) {
                $meetingLink = $reservation->getMeetingLink();
    
                // Check if the event is currently active
                $now = new \DateTime();
                $eventDate = $evenement->getDateEvent();
                $eventTime = $evenement->getHeureEvent();
    
                // Combine date and time into a single DateTime object
                $eventStart = new \DateTime($eventDate->format('Y-m-d') . ' ' . $eventTime->format('H:i:s'));
    
                // Assume the event lasts for 2 hours (you can adjust this as needed)
                $eventEnd = (clone $eventStart)->modify('+2 hours');
    
                $isEventActive = ($now >= $eventStart && $now <= $eventEnd);
            }
        }
    
        return $this->render('event/detail.html.twig', [
            'evenement' => $evenement,
            'meetingLink' => $meetingLink,
            'isEventActive' => $isEventActive,
            'mapCoordinates' => $evenement->getMapCoordinates(), // Pass map coordinates
        ]);
    }
    
    #[Route('/admin/events', name: 'admin_events')]
    public function adminIndex(Request $request, EvenementRepository $evenementRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $location = $request->query->get('location', '');
        $dateInput = $request->query->get('date', '');

        $events = $evenementRepository->searchEventsAdmin($searchTerm, $location, $dateInput);

        return $this->render('event/admin_events.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm,
            'location' => $location,
            'date' => $dateInput,
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
                $uploadDir = $this->getParameter('photo_dir');

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $imageFile->move($uploadDir, $newFilename);
                $event->setImageEvent($newFilename);
            } else {
                $event->setImageEvent('uploads/images/default.png');
            }

            // Generate a unique Jitsi Meet link for online events
            if (strtolower($event->getLieuEvent()) === 'en ligne') {
                $event->setGoogleMeetLink('https://meet.jit.si/' . uniqid());
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

    $form = $this->createForm(EventType::class, $event, [
        'is_edit' => true, // Pass a flag to indicate edit mode
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
            // Keep the old image if no new image is uploaded
            $event->setImageEvent($oldImage);
        }

        // Regenerate Jitsi Meet link if the event is online
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