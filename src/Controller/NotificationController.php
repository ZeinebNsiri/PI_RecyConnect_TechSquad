<?php

namespace App\Controller;


use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'app_notifications')]
    public function getNotifications(NotificationRepository $notificationRepository): JsonResponse
    {
        // Récupérer uniquement les notifications non lues
        $notifications = $notificationRepository->findBy(['isRead' => false], ['createdAt' => 'DESC'], 10);

        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id' => $notification->getId(),
                'message' => $notification->getMessage(),
                'createdAt' => $notification->getCreatedAt()->format('d/m/Y H:i'),
                'isRead' => $notification->isRead(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/notifications/mark-as-read', name: 'app_notifications_mark_as_read')]
    public function markAsRead(NotificationRepository $notificationRepository, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer toutes les notifications non lues
        $notifications = $notificationRepository->findBy(['isRead' => false]);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
            $em->persist($notification);
        }

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/notifications/all', name: 'app_notifications_all')]
    public function allNotifications(NotificationRepository $notificationRepository): Response
    {
        // Récupérer toutes les notifications, triées par date décroissante
        $notifications = $notificationRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }
}
