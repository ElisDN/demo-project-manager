<?php

declare(strict_types=1);

namespace App\Controller\Profile\OAuth;

use App\Model\User\UseCase\Network\Attach\Command;
use App\Model\User\UseCase\Network\Attach\Handler;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use App\Controller\ErrorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile/oauth/facebook")
 */
class FacebookController extends AbstractController
{
    private $errors;

    public function __construct(ErrorHandler $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @Route("/attach", name="profile.oauth.facebook")
     * @param ClientRegistry $clientRegistry
     * @return Response
     */
    public function connect(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry
            ->getClient('facebook_attach')
            ->redirect(['public_profile']);
    }

    /**
     * @Route("/check", name="profile.oauth.facebook_check")
     * @param ClientRegistry $clientRegistry
     * @param Handler $handler
     * @return Response
     */
    public function check(ClientRegistry $clientRegistry, Handler $handler): Response
    {
        $client = $clientRegistry->getClient('facebook_attach');

        $command = new Command(
            $this->getUser()->getId(),
            'facebook',
            $client->fetchUser()->getId()
        );

        try {
            $handler->handle($command);
            $this->addFlash('success', 'Facebook is successfully attached.');
            return $this->redirectToRoute('profile');
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('profile');
        }
    }
}