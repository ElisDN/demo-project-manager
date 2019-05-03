<?php

declare(strict_types=1);

namespace App\Annotation;

use App\Model\User\Entity\User\Id;
use App\Model\User\Entity\User\User;
use App\ReadModel\User\UserFetcher;
use App\Security\UserIdentity;
use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class RequiresUserCreditsSubscriber implements EventSubscriberInterface
{
    private $reader;
    private $users;
    private $security;
    private $redirectController;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function __construct(
        Reader $reader,
        UserFetcher $users,
        Security $security,
        RedirectController $redirectController
    )
    {
        $this->reader = $reader;
        $this->users = $users;
        $this->security = $security;
        $this->redirectController = $redirectController;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $target = $event->getController();

        if (!is_array($target)) {
            return;
        }

        [$controller, $method] = $target;

        if ($this->hasAnnotation($controller, $method, RequiresUserCredits::class)) {
            if (!$identity = $this->security->getUser()) {
                throw new AccessDeniedException();
            }

            if (!$identity instanceof UserIdentity) {
                throw new AccessDeniedException();
            }

            $user = $this->users->get($identity->getId());

            if (!$this->hasCredits($user)) {
                /** @var Session $session */
                $session = $event->getRequest()->getSession();
                $session->getFlashBag()->add('error', 'Set and confirm email for working.');
                $event->getRequest()->attributes->set('route', 'profile');
                $event->setController([$this->redirectController, 'redirectAction']);
            }
        }
    }

    private function hasCredits(User $user): bool
    {
        return $user->getEmail() !== null;
    }

    private function hasAnnotation($controller, string $method, string $class): bool
    {
        if ($this->reader->getMethodAnnotation((new \ReflectionObject($controller))->getMethod($method), $class)) {
            return true;
        }
        if ($this->reader->getClassAnnotation(new \ReflectionClass($controller), $class)) {
            return true;
        }
        return false;
    }
}
