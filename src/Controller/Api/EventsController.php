<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\ReferentEmailControllerTrait;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRegistrationRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EventsController extends Controller
{
    use ReferentEmailControllerTrait;

    /**
     * @Route("/events", name="api_committees_events")
     * @Method("GET")
     */
    public function getUpcomingCommitteesEventsAction(Request $request): Response
    {
        return new JsonResponse($this->get('app.api.event_provider')->getUpcomingEvents($request->query->getInt('type')));
    }

    /**
     * @Route("/statistics/events/count-by-month", name="app_committee_events_count_by_month")
     * @Method("GET")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function eventsCountInReferentManagedAreaAction(
        Request $request,
        AdherentRepository $adherentRepository,
        EventRepository $eventRepository,
        EventRegistrationRepository $eventRegistrationRepository,
        CommitteeRepository $committeeRepository
    ): Response {
        $referent = $this->getReferent($request, $adherentRepository);
        try {
            $filter = StatisticsParametersFilter::createFromRequest($request, $committeeRepository);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse([
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent, $filter),
            'event_participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($referent, $filter),
        ]);
    }

    /**
     * @Route("/statistics/events/count", name="app_events_count")
     * @Method("GET")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function allTypesEventsCountInReferentManagedAreaAction(
        Request $request,
        AdherentRepository $adherentRepository,
        EventRepository $eventRepository
    ): Response {
        $referent = $this->getReferent($request, $adherentRepository);

        return new JsonResponse([
            'current_total' => $eventRepository->countTotalEventsInReferentManagedAreaForCurrentMonth($referent),
            'events' => $eventRepository->countCommitteeEventsInReferentManagedArea($referent),
            'referent_events' => $eventRepository->countReferentEventsInReferentManagedArea($referent),
        ]);
    }

    /**
     * @Route("/statistics/events/count-participants", name="app_committee_events_count_participants")
     * @Method("GET")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function eventsCountInReferentManagedArea(
        Request $request,
        AdherentRepository $adherentRepository,
        EventRepository $eventRepository,
        EventRegistrationRepository $eventRegistrationRepository
    ): Response {
        $referent = $this->getReferent($request, $adherentRepository);

        return new JsonResponse([
            'total' => $eventRepository->countParticipantsInReferentManagedArea($referent),
            'participants' => $eventRegistrationRepository->countEventParticipantsInReferentManagedArea($referent),
            'participants_as_adherent' => $eventRegistrationRepository->countEventParticipantsAsAdherentInReferentManagedArea($referent),
        ]);
    }
}
