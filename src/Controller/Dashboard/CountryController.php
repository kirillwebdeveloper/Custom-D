<?php

namespace App\Controller\Dashboard;

use App\Entity\Country\Country;
use App\Form\Country\CountryType;
use App\Repository\Country\CountryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

/**
 * @Route("/dashboard/countries")
 */
class CountryController extends AbstractController
{
    /**
     * @var Breadcrumbs
     */
    private $breadcrumbs;

    /**
     * CountryController constructor.
     */
    public function __construct(Breadcrumbs $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
    }

    /**
     * @Route("/", name="dashboard_country_index", methods={"GET"})
     */
    public function index(CountryRepository $repository): Response
    {
        $this->addBreadcrumb();

        return $this->render(
            'dashboard/countries/index.html.twig',
            [
                'countries' => $repository->findAll(),
            ]
        );
    }

    /**
     * @Route("/create", name="dashboard_country_create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $this->addBreadcrumb()->addItem('breadcrumb.add');

        return $this->upsert(
            new Country(),
            $request
        );
    }

    /**
     * @Route("/{id}/edit", name="dashboard_country_edit", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function edit(Country $country, Request $request): Response
    {
        $this->addBreadcrumb()->addItem('breadcrumb.edit');

        return $this->upsert(
            $country,
            $request
        );
    }

    private function upsert(Country $country, Request $request): Response
    {
        $form = $this
            ->createForm(CountryType::class, $country)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->persist($country);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('dashboard_country_index');
        }

        return $this->render(
            'dashboard/countries/upsert.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Add breadcrumb.
     */
    private function addBreadcrumb(): Breadcrumbs
    {
        return $this->breadcrumbs
            ->addRouteItem('dashboard.breadcrumb', 'dashboard_dashboard')
            ->addRouteItem('country.breadcrumb', 'dashboard_country_index');
    }
}
