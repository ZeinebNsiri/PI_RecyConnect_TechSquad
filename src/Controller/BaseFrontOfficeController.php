<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BaseFrontOfficeController extends AbstractController
{
    #[Route('/base/front/office', name: 'app_base_front_office')]
    public function index(): Response
    {
        return $this->render('base_front_office/index.html.twig', [
            'controller_name' => 'BaseFrontOfficeController',
        ]);
    }
}
