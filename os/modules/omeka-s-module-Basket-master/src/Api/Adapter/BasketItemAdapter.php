<?php

/*
 * Copyright BibLibre, 2016
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software.  You can use, modify and/ or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software's author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user's attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software's suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace Basket\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class BasketItemAdapter extends AbstractEntityAdapter
{
    /**
     * {@inheritdoc}
     */
    protected $sortFields = [
        'id' => 'id',
        'created' => 'created',
    ];

    /**
     * {@inheritdoc}
     */
    public function getResourceName()
    {
        return 'basket_items';
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentationClass()
    {
        return 'Basket\Api\Representation\BasketItemRepresentation';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return 'Basket\Entity\BasketItem';
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        if ($this->shouldHydrate($request, 'o:user_id')) {
            $userId = $request->getValue('o:user_id');
            $entity->setUser($this->getAdapter('users')->findEntity($userId));
        }
        if ($this->shouldHydrate($request, 'o:resource_id')) {
            $resourceId = $request->getValue('o:resource_id');
            $adapter = $this->getAdapter('resources');
            $entity->setResource($adapter->findEntity($resourceId));
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (isset($query['user_id'])) {
            $userAlias = $this->createAlias();
            $qb->innerJoin(
                $this->getEntityClass() . '.user',
                $userAlias
            );
            $qb->andWhere($qb->expr()->eq(
                "$userAlias.id",
                $this->createNamedParameter($qb, $query['user_id']))
            );
        }
        if (isset($query['resource_id'])) {
            $resourceAlias = $this->createAlias();
            $qb->innerJoin(
                $this->getEntityClass() . '.resource',
                $resourceAlias
            );
            $qb->andWhere($qb->expr()->eq(
                "$resourceAlias.id",
                $this->createNamedParameter($qb, $query['resource_id']))
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sortQuery(QueryBuilder $qb, array $query)
    {
        if (is_string($query['sort_by'])) {
            $property = $this->getPropertyByTerm($query['sort_by']);
            $entityClass = $this->getEntityClass();
            if ($property) {
                $resourceAlias = $this->createAlias();
                $qb->leftJoin("$entityClass.resource", $resourceAlias);
                $valuesAlias = $this->createAlias();
                $qb->leftJoin(
                    "$resourceAlias.values", $valuesAlias,
                    'WITH', $qb->expr()->eq("$valuesAlias.property", $property->getId())
                );
                $qb->addOrderBy(
                    "GROUP_CONCAT($valuesAlias.value ORDER BY $valuesAlias.id)",
                    $query['sort_order']
                );
            } else {
                parent::sortQuery($qb, $query);
            }
        }
    }

    /**
     * Get a property entity by JSON-LD term.
     *
     * @param string $term
     * @return EntityInterface
     */
    protected function getPropertyByTerm($term)
    {
        if (!$this->isTerm($term)) {
            return null;
        }

        list($prefix, $localName) = explode(':', $term);
        $dql = '
            SELECT p
            FROM Omeka\Entity\Property p
            JOIN p.vocabulary v WHERE p.localName = :localName
                AND v.prefix = :prefix
        ';

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters([
                'localName' => $localName,
                'prefix' => $prefix
            ])->getOneOrNullResult();
    }
}
