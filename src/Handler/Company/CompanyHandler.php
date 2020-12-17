<?php

namespace App\Handler\Company;

use App\Modele\Company\FilterSearchCompany;
use App\Repository\Company\CompanyRepository;
use App\Service\Pagination\Paginator;
use Symfony\Component\HttpFoundation\ParameterBag;

class CompanyHandler
{
    /**
     * @var CompanyRepository
     */
    private $repository;

    public function __construct(CompanyRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findCompaniesByUserQuery(ParameterBag $queryParameters): Paginator
    {
        $filter = new FilterSearchCompany();

        $filter->setSearch($queryParameters->get('term', null));
        if ($queryParameters->has('investor')) {
            $filter->setInvestor($queryParameters->getBoolean('investor', false));
        }
        if ($queryParameters->has('associate')) {
            $filter->setAssociate($queryParameters->getBoolean('associate', false));
        }
        if ($queryParameters->has('ubo')) {
            $filter->setUbo($queryParameters->getBoolean('ubo', false));
        }

        return $this->repository->search($filter);
    }
}
