<?php

declare(strict_types=1);

namespace App\Controller;

use App\Annotation\RequiresUserCredits;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RequiresUserCredits()
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('app/home.html.twig');
    }
}