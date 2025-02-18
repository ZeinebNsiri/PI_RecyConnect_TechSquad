<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Evenement;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    #[Route('/event/{id}/register', name: 'event_registration', methods: ['GET', 'POST'])]
    public function register(Request $request, EntityManagerInterface $entityManager, Evenement $evenement): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(RegistrationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setEventId($evenement);
            $nbPlacesReservees = $reservation->getNbPlaces();
            $evenement->setNbRestant($evenement->getNbRestant() - $nbPlacesReservees);

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre inscription a été enregistrée avec succès.');
            return $this->redirectToRoute('reservations_list');
        }

        return $this->render('reservation/register.html.twig', [
            'form' => $form->createView(),
            'evenement' => $evenement,
        ]);
    }

    #[Route('/reservations', name: 'reservations_list')]
    public function listReservations(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findBy([
            'status' => 'active',
        ]);

        return $this->render('reservation/list.html.twig', [
            'reservations' => $reservations,
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

    #[Route('/admin/reservations', name: 'admin_reservations_list')]
    public function adminListReservations(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

        return $this->render('reservation/admin_listRes.html.twig', [
            'reservations' => $reservations,
        ]);
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

    #[Route('/admin/reservation/{id}/edit', name: 'admin_reservation_edit', methods: ['GET', 'POST'])]
    public function adminEditReservation(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            $this->addFlash('error', 'Réservation non trouvée.');
            return $this->redirectToRoute('admin_reservations_list');
        }

        $form = $this->createForm(RegistrationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été modifiée avec succès.');
            return $this->redirectToRoute('admin_reservation_show', ['id' => $reservation->getId()]);
        }

        return $this->render('reservation/admin_editRes.html.twig', [
            'form' => $form->createView(),
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
}