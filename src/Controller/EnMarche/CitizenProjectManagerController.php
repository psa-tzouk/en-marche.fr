<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenProject\CitizenProjectContactActorsCommand;
use AppBundle\CitizenProject\CitizenProjectContactActorsCommandHandler;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectUpdateCommand;
use AppBundle\Entity\CitizenProject;
use AppBundle\Form\CitizenProjectCommandType;
use AppBundle\Form\CitizenProjectContactActorsType;
use AppBundle\Utils\GroupUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/projets-citoyens/{slug}")
 * @Security("is_granted('ADMINISTRATE_CITIZEN_PROJECT', citizenProject)")
 */
class CitizenProjectManagerController extends Controller
{
    /**
     * @Route("/editer", name="app_citizen_project_manager_edit")
     * @Method("GET|POST")
     */
    public function editAction(Request $request, CitizenProject $citizenProject, CitizenProjectManager $manager): Response
    {
        $command = CitizenProjectUpdateCommand::createFromCitizenProject($citizenProject);
        $form = $this->createForm(CitizenProjectCommandType::class, $command, [
            'from_turnkey_project' => $citizenProject->isFromTurnkeyProject(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.citizen_project.update_handler')->handle($command);
            $this->addFlash('info', 'citizen_project.update.success');

            return $this->redirectToRoute('app_citizen_project_manager_edit', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        return $this->render('citizen_project/edit.html.twig', [
            'form' => $form->createView(),
            'citizen_project' => $citizenProject,
            'administrators' => $manager->getCitizenProjectAdministrators($citizenProject),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
        ]);
    }

    /**
     * @Route("/acteurs/contact", name="app_citizen_project_contact_actors")
     * @Method("POST")
     */
    public function contactActorsAction(Request $request, CitizenProject $citizenProject, CitizenProjectManager $citizenProjectManager): Response
    {
        if (!$this->isCsrfTokenValid('citizen_project.contact_actors', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact actors.');
        }

        $uuids = GroupUtils::getUuidsFromJson($request->request->get('contacts', ''));
        $adherents = GroupUtils::removeUnknownAdherents($uuids, $citizenProjectManager->getCitizenProjectMembers($citizenProject));
        $command = new CitizenProjectContactActorsCommand($adherents, $this->getUser());
        $contacts = GroupUtils::getUuidsFromAdherents($adherents);

        if (empty($contacts)) {
            $this->addFlash('info', 'citizen_project.contact_actors.none');

            return $this->redirectToRoute('app_citizen_project_list_actors', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        $form = $this->createForm(CitizenProjectContactActorsType::class, $command)
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(CitizenProjectContactActorsCommandHandler::class)->handle($command);
            $this->addFlash('info', 'citizen_project.contact_actors.success');

            return $this->redirectToRoute('app_citizen_project_list_actors', [
                'slug' => $citizenProject->getSlug(),
            ]);
        }

        return $this->render('citizen_project/contact.html.twig', [
            'citizen_project' => $citizenProject,
            'administrators' => $citizenProjectManager->getCitizenProjectAdministrators($citizenProject),
            'contacts' => GroupUtils::getUuidsFromAdherents($adherents),
            'form_committee_support' => $this->createForm(FormType::class)->createView(),
            'form' => $form->createView(),
        ]);
    }
}
