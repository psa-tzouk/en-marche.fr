<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\ReferentEmailControllerTrait;
use AppBundle\Entity\Committee;
use AppBundle\History\CommitteeMembershipHistoryHandler;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommitteesController extends Controller
{
    use ReferentEmailControllerTrait;

    /**
     * @Route("/committees", name="api_committees")
     * @Method("GET")
     */
    public function getApprovedCommitteesAction(): Response
    {
        return new JsonResponse($this->get('app.api.committee_provider')->getApprovedCommittees());
    }

    /**
     * @Route("/statistics/committees/count-for-referent-area", name="app_committees_count_for_referent_area")
     * @Method("GET")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function getCommitteeCountersAction(
        Request $request,
        AdherentRepository $adherentRepository,
        CommitteeRepository $committeeRepository
    ): Response {
        $referent = $this->getReferent($request, $adherentRepository);

        return new JsonResponse([
            'committees' => $committeeRepository->countApprovedForReferent($referent),
            'members' => $adherentRepository->countMembersByGenderForReferent($referent),
            'supervisors' => $adherentRepository->countSupervisorsByGenderForReferent($referent),
        ]);
    }

    /**
     * @Route("/statistics/committees/members/count-by-month", name="app_committee_members_count_by_month_for_referent_area")
     * @Method("GET")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function getMembersCommitteeCountAction(
        Request $request,
        AdherentRepository $adherentRepository,
        CommitteeMembershipHistoryHandler $committeeMembershipHistoryHandler
    ): Response {
        $referent = $this->getReferent($request, $adherentRepository);

        $filter = StatisticsParametersFilter::createFromRequest($request, $this->getDoctrine()->getRepository(Committee::class));

        return new JsonResponse(['committee_members' => $committeeMembershipHistoryHandler->queryCountByMonth($referent, 6, $filter)]);
    }

    /**
     * @Route("/statistics/committees/top-5-in-referent-area", name="app_most_active_committees")
     * @Method("GET")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function getTopCommitteesInReferentManagedAreaAction(
        Request $request,
        AdherentRepository $adherentRepository,
        CommitteeRepository $committeeRepository
    ): Response {
        $referent = $this->getReferent($request, $adherentRepository);

        return new JsonResponse([
            'most_active' => $committeeRepository->retrieveMostActiveCommitteesInReferentManagedArea($referent),
            'least_active' => $committeeRepository->retrieveLeastActiveCommitteesInReferentManagedArea($referent),
        ]);
    }
}
