<?php

declare(strict_types=1);

namespace App\Controller\Api\Auth;

use App\Model\User\UseCase\SignUp;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignUpController extends AbstractController
{
    private $serializer;
    private $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/auth/signup", name="auth.signup", methods={"POST"})
     * @param Request $request
     * @param SignUp\Request\Handler $handler
     * @return Response
     */
    public function request(Request $request, SignUp\Request\Handler $handler): Response
    {
        /** @var SignUp\Request\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), SignUp\Request\Command::class, 'json');

        $violations = $this->validator->validate($command);
        if (\count($violations)) {
            $json = $this->serializer->serialize($violations, 'json');
            return new JsonResponse($json, 400, [], true);
        }

        $handler->handle($command);

        return $this->json([], 201);
    }
}
