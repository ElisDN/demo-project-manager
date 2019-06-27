<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("", name="home", methods={"GET"})
     * @return Response
     */
    public function home(): Response
    {
        return $this->json([
            'name' => 'JSON API',
        ]);
    }
}
