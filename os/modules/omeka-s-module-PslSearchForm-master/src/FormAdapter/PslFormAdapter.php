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

namespace PslSearchForm\FormAdapter;

use Search\Query;
use Search\FormAdapter\FormAdapterInterface;

class PslFormAdapter implements FormAdapterInterface
{
    public function getLabel()
    {
        return 'PSL';
    }

    public function getFormClass()
    {
        return  'PslSearchForm\Form\PslForm';
    }

    public function getFormPartial()
    {
        return 'psl-search-form/psl-search-form';
    }

    public function getConfigFormClass()
    {
        return 'PslSearchForm\Form\PslFormConfigFieldset';
    }

    public function toQuery($data, $formSettings)
    {
        $query = new Query();

        if (isset($formSettings['is_public_field'])) {
            $query->addFilter($formSettings['is_public_field'], true);
        }

        if (isset($data['q'])) {
            $query->setQuery($data['q']);
        }

        if (!empty($data['map']['spatial-coverage'])) {
            $field = $formSettings['spatial_coverage_field'];
            $query->addFilter($field, $data['map']['spatial-coverage']);
        }

        if (isset($data['date']['from']) || isset($data['date']['to'])) {
            $field = $formSettings['date_range_field'];
            $start = $data['date']['from'];
            $end = $data['date']['to'];
            if ($start || $end) {
                $query->addDateRangeFilter($field, $start, $end);
            }
        }

        if (isset($data['itemSet']['ids'])) {
            $field = $formSettings['item_set_id_field'];
            $query->addFilter($field, $data['itemSet']['ids']);
        }

        if (isset($data['text']['filters'])) {
            foreach ($data['text']['filters'] as $filter) {
                if (!empty($filter['value'])) {
                    $query->addFilter($filter['field'], $filter['value']);
                }
            }
        }

        if (!empty($data['text']['creation-year'])) {
            $field = $formSettings['creation_year_field'];
            $query->addFilter($field, $data['text']['creation-year']);
        }

        return $query;
    }
}
