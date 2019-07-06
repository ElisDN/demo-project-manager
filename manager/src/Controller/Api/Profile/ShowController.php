<?php

declare(strict_types=1);

namespace App\Controller\Api\Profile;

use App\Model\User\Entity\User\Network;
use App\ReadModel\User\UserFetcher;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/profile",
     *     tags={"Profile"},
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="name", type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *             ),
     *             @OA\Property(property="networks", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="identity", type="string"),
     *             ))
     *         )
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/profile", name="profile", methods={"GET"})
     * @param UserFetcher $users
     * @return Response
     */
    public function show(UserFetcher $users): Response
    {
        $user = $users->get($this->getUser()->getId());

        return $this->json([
            'id' => $user->getId()->getValue(),
            'email' => $user->getEmail() ? $user->getEmail()->getValue() : null,
            'name' => [
                'first' => $user->getName()->getFirst(),
                'last' => $user->getName()->getLast(),
            ],
            'networks' => array_map(static function (Network $network): array {
                return [
                    'name' => $network->getNetwork(),
                    'identity' => $network->getIdentity(),
                ];
            }, $user->getNetworks())
        ]);
    }
}
