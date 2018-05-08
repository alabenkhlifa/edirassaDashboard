<?php
/**
 * Created by PhpStorm.
 * User: ALA
 * Date: 08/05/2018
 * Time: 22:35
 */

namespace DashboardBundle\Repository;

use Doctrine\ORM\EntityRepository;
class ServerRepository extends EntityRepository

{
    public function findCount(){
        return $this->getEntityManager()
            ->createQuery(
                'SELECT COUNT (s) FROM DashboardBundle:Server s'
            )
            ->getSingleResult()[1];
    }

}