<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * Class AbstractSaverRepository.
 */
abstract class AbstractSaverRepository extends ServiceEntityRepository
{
    public function save($entity = null)
    {
        $entityManager = $this->_em;
        if ($entity) {
            if (is_iterable($entity)) {
                foreach ($entity as $en) {
                    $entityManager->persist($en);
                }
            } else {
                $entityManager->persist($entity);
            }
        }
        $entityManager->flush();
    }

    public function remove($entity = null)
    {
        if (!$entity) {
            return;
        }
        $entityManager = $this->_em;
        if (is_iterable($entity)) {
            foreach ($entity as $en) {
                $entityManager->remove($en);
            }
        } else {
            $entityManager->remove($entity);
        }
        $entityManager->flush();
    }
}
