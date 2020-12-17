<?php

namespace App\Controller\Dashboard\Agency;

use App\Entity\Agency\Agency;
use App\Entity\Company\Person;
use App\Form\Agency\AgencyType;
use App\Form\Agency\SearchAgencyType;
use App\Form\Company\PersonEmployeeType;
use App\Modele\Agency\FilterSearchAgency;
use App\Modele\Agency\FilterSearchMandate;
use App\Modele\Company\FilterSearchPerson;
use App\Repository\Agency\AgencyRepository;
use App\Repository\Agency\MandateRepository;
use App\Repository\Company\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

/**
 * @Route("/dashboard/agencies")
 */
class AgencyController extends AbstractController
{
    /**
     * @var Breadcrumbs
     */
    private $breadcrumbs;

    /**
     * AgencyController constructor.
     */
    public function __construct(Breadcrumbs $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @Route("/", name="dashboard_agency_view_index", methods={"GET"})
     * @Route("/", name="dashboard_agency_index", methods={"GET"})
     */
    public function index(AgencyRepository $repository, Request $request): Response
    {
        $this->addBreadcrumb();

        $search     = new FilterSearchAgency();
        $searchForm = $this->createForm(SearchAgencyType::class, $search)->handleRequest($request);

        return $this->render(
            'dashboard/agency/index.html.twig',
            [
                'agencies'   => $repository->search($search, $request->query->getInt('page', 1)),
                'searchForm' => $searchForm->createView(),
            ]
        );
    }

    /**
     * @Route("/create", name="dashboard_agency_create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $this->addBreadcrumb()->addItem('breadcrumb.add');

        return $this->upsert(
            new Agency(),
            $request
        );
    }

    /**
     * @Route("/{id}/edit", name="dashboard_agency_edit", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function edit(Agency $agency, Request $request): Response
    {
        $this->addBreadcrumb()->addItem('breadcrumb.edit');

        return $this->upsert(
            $agency,
            $request
        );
    }

    /**
     * @Route("/{id}/view/mandate", name="dashboard_agency_view_mandate", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function viewMandate(Agency $agency, Request $request, MandateRepository $mandateRepository): Response
    {
        $this->addBreadcrumb($agency)->addItem('mandate.breadcrumb');

        $filter = (new FilterSearchMandate())
            ->setAgency($agency);

        return $this->render(
            'dashboard/agency/view/mandate.html.twig',
            [
                'agency'   => $agency,
                'mandates' => $mandateRepository->search($filter, $request->query->getInt('page', 1)),
            ]
        );
    }

    /**
     * @Route("/{id}/view/employee", name="dashboard_agency_view_employee", methods={"GET"}, requirements={"id": "\d+"})
     */
    public function viewEmployee(Agency $agency, Request $request, PersonRepository $personRepository): Response
    {
        $this->addBreadcrumb($agency)->addItem('person.breadcrumb');

        $filter = (new FilterSearchPerson())
            ->setCompany($agency->getCompany());

        return $this->render(
            'dashboard/agency/view/employee.html.twig',
            [
                'agency'    => $agency,
                'employees' => $personRepository->search($filter, $request->query->getInt('page', 1)),
            ]
        );
    }

    /**
     * @Route("/{id}/employee/add", name="dashboard_agency_add_employee", requirements={"id": "\d+"})
     */
    public function addEmployee(Agency $agency, Request $request): Response
    {
        $this->addBreadcrumb($agency)
             ->addRouteItem('person.breadcrumb', 'dashboard_agency_view_employee', ['id' => $agency->getCompany()->getId()])
             ->addItem('breadcrumb.add');

        return $this->upsertEmployee($agency, new Person(), $request);
    }

    /**
     * @Route("/{id}/employee/{person}/edit", name="dashboard_agency_edit_employee", requirements={"id": "\d+", "person": "\d+"})
     */
    public function editEmployee(Agency $agency, Person $person, Request $request): Response
    {
        $this->addBreadcrumb($agency)
             ->addRouteItem('person.breadcrumb', 'dashboard_agency_view_employee', ['id' => $agency->getCompany()->getId()])
             ->addItem('breadcrumb.edit');

        return $this->upsertEmployee($agency, $person, $request);
    }

    private function upsertEmployee(Agency $agency, Person $person, Request $request): Response
    {
        $person->setCompany($agency->getCompany());

        $form = $this
            ->createForm(PersonEmployeeType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();

            return $this->redirectToRoute(
                'dashboard_agency_view_employee',
                [
                    'id' => $agency->getCompany()->getId(),
                ]
            );
        }

        return $this->render(
            'dashboard/company/person/view/upsert.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Response|JsonResponse.
     */
    private function upsert(Agency $agency, Request $request): Response
    {
        $form = $this
            ->createForm(AgencyType::class, $agency)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($agency);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('dashboard_agency_index');
        }

        return $this->render(
            'dashboard/agency/upsert.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Add breadcrumb.
     */
    private function addBreadcrumb(?Agency $agency = null): Breadcrumbs
    {
        $this->breadcrumbs
            ->addRouteItem('dashboard.breadcrumb', 'dashboard_dashboard')
            ->addRouteItem('agency.breadcrumb', 'dashboard_agency_index');

        if ($agency) {
            $this->breadcrumbs->addRouteItem($agency, 'dashboard_agency_view_mandate', ['id' => $agency->getCompany()->getId()]);
        }

        return $this->breadcrumbs;
    }
}
