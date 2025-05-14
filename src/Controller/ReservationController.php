<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Evenement;
use App\Repository\ReservationRepository;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/event/{id}/register', name: 'event_registration', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        Evenement $evenement,
        ReservationRepository $reservationRepository
    ): Response {
        $reservation = new Reservation();
        $form = $this->createForm(RegistrationType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the event and user for the reservation
            $reservation->setEventId($evenement);
            $reservation->setUserId($this->getUser());
    
            // Update the remaining places for the event
            $nbPlacesReservees = $reservation->getNbPlaces();
            $evenement->setNbRestant($evenement->getNbRestant() - $nbPlacesReservees);
    
            // Generate or reuse a meeting link for online events
            if (strtolower($evenement->getLieuEvent()) === 'en ligne') {
                // Check if a meeting link already exists for this event
                $existingReservation = $reservationRepository->findOneBy([
                    'event' => $evenement,
                ]);
    
                if ($existingReservation && $existingReservation->getMeetingLink()) {
                    // Use the existing meeting link
                    $reservation->setMeetingLink($existingReservation->getMeetingLink());
                } else {
                    // Generate a new meeting link for the event
                    $meetingLink = 'https://meet.jit.si/' . uniqid();
                    $reservation->setMeetingLink($meetingLink);
                }
            }
    
            // Save the reservation
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            // Check if the event is currently active
            $now = new \DateTime();
            $eventDate = $evenement->getDateEvent();
            $eventTime = $evenement->getHeureEvent();
    
            // Combine date and time into a single DateTime object
            $eventStart = new \DateTime($eventDate->format('Y-m-d') . ' ' . $eventTime->format('H:i:s'));
    
            // Assume the event lasts for 2 hours (you can adjust this as needed)
            $eventEnd = (clone $eventStart)->modify('+2 hours');
    
            $isEventActive = ($now >= $eventStart && $now <= $eventEnd);
    
            // Pass the meeting link and event active status to the template
            return $this->render('event/detail.html.twig', [
                'evenement' => $evenement,
                'meetingLink' => $reservation->getMeetingLink(),
                'isEventActive' => $isEventActive,
                'isUserRegistered' => true, // User is now registered
            ]);
        }
    
        return $this->render('reservation/register.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
        ]);
    }
    
#[Route('/reservations', name: 'reservations_list')]
public function listReservations(Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $now = new \DateTime();

    // Get search parameters from the request
    $eventName = $request->query->get('eventName');
    $status = $request->query->get('status');

    // Build the query
    $qb = $entityManager->getRepository(Reservation::class)->createQueryBuilder('r')
        ->leftJoin('r.event', 'e')
        ->where('r.user_id = :user')
        ->setParameter('user', $user);

    // Apply filters
    if ($eventName) {
        $qb->andWhere('e.nomEvent LIKE :eventName')
           ->setParameter('eventName', '%' . $eventName . '%');
    }

    if ($status) {
        if ($status === 'upcoming') {
            $qb->andWhere('e.dateEvent >= :now')
               ->setParameter('now', $now->format('Y-m-d'));
        } else {
            $qb->andWhere('r.status = :status')
               ->setParameter('status', $status);
        }
    }

    // Order by event date and time
    $qb->orderBy('e.dateEvent', 'ASC')
       ->addOrderBy('e.heureEvent', 'ASC');

    $reservations = $qb->getQuery()->getResult();

    return $this->render('reservation/list.html.twig', [
        'reservations' => $reservations,
        'now' => $now,
        'eventName' => $eventName,
        'status' => $status,
    ]);
}
#[Route('/reservation/{id}', name: 'reservation_show', methods: ['GET'])]
public function show(Reservation $reservation): Response
{
    $now = new \DateTime();

    // Fetch the reservation details
    return $this->render('reservation/show.html.twig', [
        'reservation' => $reservation,
        'now' => $now,
    ]);
}
    #[Route('/reservation/edit/{id}', name: 'reservation_edit', methods: ['GET', 'POST'])]
    public function editReservation(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('reservations_list');
        }

        $event = $reservation->getEventId();
        $oldNbPlaces = $reservation->getNbPlaces();

        $form = $this->createForm(RegistrationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newNbPlaces = $form->get('nb_places')->getData();

            if ($oldNbPlaces != $newNbPlaces) {
                $reservation->setNbPlaces($newNbPlaces);
                $event->setNbRestant($event->getNbRestant() + ($oldNbPlaces - $newNbPlaces));
            }

            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été modifiée avec succès.');
            return $this->redirectToRoute('reservations_list');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/cancel/{id}/confirm', name: 'reservation_cancel_confirm', methods: ['GET'])]
    public function confirmCancelReservation(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('reservations_list');
        }

        return $this->render('reservation/cancel_confirm.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/cancel/{id}', name: 'reservation_cancel', methods: ['POST'])]
    public function cancelReservation(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('reservations_list');
        }

        $event = $reservation->getEventId();
        $event->setNbRestant($event->getNbRestant() + $reservation->getNbPlaces());

        $reservation->setStatus('canceled');
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a été annulée avec succès.');
        return $this->redirectToRoute('reservations_list');
    }
    

    #[Route('/admin/reservation/{id}', name: 'admin_reservation_show', methods: ['GET'])]
    public function adminShowReservation(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin_reservations_list');
        }

        return $this->render('reservation/admin_showRes.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/admin/reservation/delete/{id}', name: 'delete_reservation_confirm', methods: ['GET'])]
    public function deleteConfirmRes(Reservation $reservation): Response
    {
        return $this->render('reservation/delete_confirm.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/admin/reservations', name: 'admin_reservations_list')]
    public function adminListReservations(Request $request, ReservationRepository $reservationRepository): Response
    {
        // Get search parameters
        $eventName = $request->query->get('eventName');
        $username = $request->query->get('username');
        $status = $request->query->get('status');

        // Use the repository method to search for reservations
        $reservations = $reservationRepository->searchReservations($eventName, $username, $status);

        return $this->render('reservation/admin_listRes.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/admin/reservation/delete/{id}/confirm', name: 'admin_reservation_delete', methods: ['POST'])]
    public function adminDeleteReservation(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin_reservations_list');
        }

        $event = $reservation->getEventId();
        $event->setNbRestant($event->getNbRestant() + $reservation->getNbPlaces());

        $entityManager->remove($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'La réservation a été supprimée avec succès.');
        return $this->redirectToRoute('admin_reservations_list');
    }

    #[Route('/dashboard/count', name: 'admin_stats')]
    public function stats(ReservationRepository $reservationRepository): Response
{
    $popularEvents = $reservationRepository->findMostPopularEvents();
    $capacityUtilization = $reservationRepository->findEventCapacityUtilization();

    return $this->render('reservation/adminStat.html.twig', [
        'popularEvents' => $popularEvents,
        'capacityUtilization' => $capacityUtilization,
    ]);
}
}