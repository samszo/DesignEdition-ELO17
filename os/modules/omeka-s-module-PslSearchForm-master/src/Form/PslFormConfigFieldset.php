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

namespace PslSearchForm\Form;

use Zend\Form\Fieldset;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Search\Query;

class PslFormConfigFieldset extends Fieldset
{
    use TranslatorAwareTrait;

    public function init()
    {
        $translator = $this->getTranslator();

        $this->add($this->getAdvancedFieldsFieldset());

        $this->add([
            'name' => 'is_public_field',
            'type' => 'Select',
            'options' => [
                'label' => $translator->translate('Is Public field'),
                'value_options' => $this->getFieldsOptions(),
                'empty_option' => $translator->translate('None'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'date_range_field',
            'type' => 'Select',
            'options' => [
                'label' => $translator->translate('Date range field'),
                'value_options' => $this->getFieldsOptions(),
                'empty_option' => $translator->translate('None'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'item_set_id_field',
            'type' => 'Select',
            'options' => [
                'label' => $translator->translate('Item set id field'),
                'value_options' => $this->getFieldsOptions(),
                'empty_option' => $translator->translate('None'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'creation_year_field',
            'type' => 'Select',
            'options' => [
                'label' => $translator->translate('Creation year field'),
                'value_options' => $this->getFieldsOptions(),
                'empty_option' => $translator->translate('None'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'spatial_coverage_field',
            'type' => 'Select',
            'options' => [
                'label' => $translator->translate('Spatial coverage field'),
                'value_options' => $this->getFieldsOptions(),
                'empty_option' => $translator->translate('None'),
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add($this->getLocationsFieldset());
    }

    protected function getAdvancedFieldsFieldset()
    {
        $translator = $this->getTranslator();

        $advancedFieldsFieldset = new Fieldset('advanced-fields');
        $advancedFieldsFieldset->setLabel($translator->translate('Advanced search fields'));
        $advancedFieldsFieldset->setAttribute('data-sortable', '1');

        $fields = $this->getAvailableFields();
        $weights = range(0, count($fields));
        $weight_options = array_combine($weights, $weights);
        $weight = 0;
        foreach ($fields as $field) {
            $fieldset = new Fieldset($field['name']);
            $fieldset->setLabel(sprintf('%s (%s)', $field['label'], $field['name']));

            $fieldset->add([
                'name' => 'enabled',
                'type' => 'Checkbox',
                'options' => [
                    'label' => $translator->translate('Enabled'),
                ],
            ]);

            $fieldset->add([
                'name' => 'weight',
                'type' => 'Select',
                'options' => [
                    'label' => $translator->translate('Weight'),
                    'value_options' => $weight_options,
                ],
                'attributes' => [
                    'value' => $weight++,
                ],
            ]);

            $advancedFieldsFieldset->add($fieldset);
        }

        return $advancedFieldsFieldset;
    }

    protected function getAvailableFields()
    {
        $searchPage = $this->getOption('search_page');
        $searchAdapter = $searchPage->index()->adapter();
        return $searchAdapter->getAvailableFields();
    }

    protected function getFieldsOptions()
    {
        $options = [];
        foreach ($this->getAvailableFields() as $name => $field) {
            $options[$name] = sprintf('%s (%s)', $field['label'], $name);
        }
        return $options;
    }

    protected function getLocationsFieldset()
    {
        $translator = $this->getTranslator();

        $fieldset = new Fieldset('locations');

        $locations = $this->getLocations();
        if (!empty($locations)) {
            $fieldset->setLabel($translator->translate('Locations'));

            foreach ($this->getLocations() as $location) {
                $fieldset->add([
                    'type' => 'Text',
                    'name' => $location,
                    'options' => [
                        'label' => $location,
                    ],
                    'attributes' => [
                        'placeholder' => $translator->translate('Latitude, Longitude'),
                    ],
                ]);
            }
        }

        return $fieldset;
    }

    protected function getLocations()
    {
        $searchPage = $this->getOption('search_page');
        $searchQuerier = $searchPage->index()->querier();
        $spatialCoverageField = $searchPage->settings()['form']['spatial_coverage_field'];

        $locations = [];
        if ($spatialCoverageField) {
            $query = new Query;
            $query->setResources(['items']);
            $query->addFacetField($spatialCoverageField);

            $response = $searchQuerier->query($query);

            $facetCounts = $response->getFacetCounts();
            foreach ($facetCounts[$spatialCoverageField] as $facetCount) {
                $locations[] = $facetCount['value'];
            }
        }

        return $locations;
    }
}
