<?php

namespace App\Repository\Company;

use App\Entity\Company\Associate\Associate;
use App\Entity\Company\Company;
use App\Entity\Company\Ubo;
use App\Entity\Investor\Investor;
use App\Modele\Company\FilterSearchCompany;
use App\Service\Pagination\Paginator;
use App\Service\Pagination\PaginatorFactory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    /**
     * @var PaginatorFactory
     */
    private $paginator;

    /**
     * AddressRepository constructor.
     */
    public function __construct(ManagerRegistry $registry, PaginatorFactory $paginator)
    {
        parent::__construct($registry, Company::class);
        $this->paginator = $paginator;
    }

    public function search(FilterSearchCompany $filter, int $page = 1): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('company');

        $queryBuilder->addOrderBy('company.id', 'DESC');

        $this->addConditions($queryBuilder, $filter);

        return $this->paginator->getPaginator(
            $queryBuilder,
            $page,
        );
    }

    private function addConditions(QueryBuilder $queryBuilder, FilterSearchCompany $filter): void
    {
        if (null !== $filter->getSearch()) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('company.name', ':search'),
                    $queryBuilder->expr()->like('company.email', ':search')
                )
            )->setParameter(
                ':search',
                '%'.preg_replace('/[^A-Z0-9]/i', '%', $filter->getSearch()).'%'
            );
        }
        if (true === $filter->isWithEmail()) {
            $queryBuilder->andWhere(
                'company.email IS NOT NULL'
            );
        } elseif (false === $filter->isWithEmail()) {
            $queryBuilder->andWhere(
                'company.email IS NULL'
            );
        }

        if (true === $filter->isInvestor()) {
            $queryBuilder->innerJoin(Investor::class, 'investor', Join::WITH, 'investor.company = company');
        } elseif (false === $filter->isInvestor()) {
            $queryBuilder->leftJoin(Investor::class, 'investor', Join::WITH, 'investor.company = company')
                         ->andWhere(
                             'investor IS NULL'
                         );
        }

        if (true === $filter->isAssociate()) {
            $queryBuilder->innerJoin(Associate::class, 'associate', Join::WITH, 'associate.company = company');
        } elseif (false === $filter->isAssociate()) {
            $queryBuilder->leftJoin(Associate::class, 'associate', Join::WITH, 'associate.company = company')
                         ->andWhere(
                             'associate IS NULL'
                         );
        }

        if (true === $filter->isUbo()) {
            $queryBuilder->innerJoin(Ubo::class, 'ubo', Join::WITH, 'ubo.company = company');
        } elseif (false === $filter->isUbo()) {
            $queryBuilder->leftJoin(Ubo::class, 'ubo', Join::WITH, 'ubo.company = company')
                         ->andWhere(
                             'ubo IS NULL'
                         );
        }
    }
}
