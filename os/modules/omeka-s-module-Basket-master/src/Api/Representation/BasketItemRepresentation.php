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

namespace Basket\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class BasketItemRepresentation extends AbstractEntityRepresentation
{
    /**
     * {@inheritdoc}
     */
    public function getJsonLdType()
    {
        return 'o:BasketItem';
    }

    public function getJsonLd()
    {
        $entity = $this->resource;

        return [
            'o:user_id' => $entity->getUser()->getId(),
            'o:resource_id' => $entity->getResource()->getId(),
            'o:created' => $this->getDateTime($entity->getCreated()),
        ];
    }

    public function user()
    {
        $adapter = $this->getAdapter('users');

        return $adapter->getRepresentation($this->resource->getUser());
    }

    public function resource()
    {
        $adapter = $this->getAdapter('resources');

        return $adapter->getRepresentation($this->resource->getResource());
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function getEntity()
    {
        return $this->resource;
    }
}
