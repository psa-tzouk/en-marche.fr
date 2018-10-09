<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait ReferentEmailControllerTrait
{
    /**
     * @throws BadRequestHttpException when query parameter is not correct
     */
    public function getReferent(Request $request, AdherentRepository $adherentRepository): Adherent
    {
        if (!$email = $request->query->get('email')) {
            throw new BadRequestHttpException('The parameter "email" is required.');
        }
        $referent = $adherentRepository->findOneByEmail($email);
        if (!$referent || !$referent->isReferent()) {
            throw new BadRequestHttpException('The parameter "email" should be an email of a referent.');
        }

        return $referent;
    }
}
