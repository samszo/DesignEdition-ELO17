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
use Zend\Form\Form;
use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\I18n\Translator\TranslatorAwareTrait;
use Search\Query;
use Search\Querier\Exception\QuerierException;

class PslForm extends Form implements TranslatorAwareInterface
{
    use TranslatorAwareTrait;

    protected $apiManager;

    public function init()
    {
        $translator = $this->getTranslator();

        $this->remove('csrf');

        $this->add([
            'name' => 'q',
            'type' => 'Text',
            'options' => [
                'label' => $translator->translate('Search'),
            ],
            'attributes' => [
                'placeholder' => $translator->translate('Search'),
            ],
        ]);

        $this->add($this->mapFieldset());
        $this->add($this->dateFieldset());
        $this->add($this->itemSetFieldset());
        $this->add($this->textFieldset());

        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => [
                'value' => $translator->translate('Submit'),
                'type' => 'submit',
            ],
        ]);
    }

    public function getInputFilter()
    {
        $inputFilter = parent::getInputFilter();

        $itemSetIds = $inputFilter->get('itemSet')->get('ids');
        $itemSetIds->setRequired(false);

        return $inputFilter;
    }

    public function setApiManager($apiManager)
    {
        $this->apiManager = $apiManager;
    }

    public function getApiManager()
    {
        return $this->apiManager;
    }

    public function setFormElementManager($formElementManager)
    {
        $this->formElementManager = $formElementManager;
    }

    public function getFormElementManager()
    {
        return $this->formElementManager;
    }

    public function getLocations()
    {
        $searchPage = $this->getOption('search_page');
        $settings = $searchPage->settings();
        $formSettings = $settings['form'];
        $locations = $formSettings['locations'];
        $spatialCoverageField = $formSettings['spatial_coverage_field'];

        $searchQuerier = $searchPage->index()->querier();

        $query = new Query;
        $query->setResources(['items']);
        $query->addFacetField($spatialCoverageField);

        $locationsOut = [];
        try {
            $response = $searchQuerier->query($query);

            $facetCounts = $response->getFacetCounts();
            if (isset($facetCounts[$spatialCoverageField])) {
                foreach ($facetCounts[$spatialCoverageField] as $facetCount) {
                    $name = $facetCount['value'];
                    if (isset($locations[$name])) {
                        $locationsOut[$name] = [
                            'coords' => $locations[$name],
                            'count' => $facetCount['count'],
                        ];
                    }
                }
            }
        } catch (QuerierException $e) {
            error_log($e->getMessage());
        }


        return $locationsOut;
    }

    protected function mapFieldset()
    {
        $fieldset = new Fieldset('map');

        $fieldset->add([
            'type' => 'Hidden',
            'name' => 'spatial-coverage',
        ]);

        return $fieldset;
    }

    protected function dateFieldset()
    {
        $fieldset = new Fieldset('date');
        $fieldset->setLabel($this->translate('date'));

        $fieldset->add([
            'name' => 'from',
            'type' => 'Text',
            'options' => [
                'label' => $this->translate('From year'),
            ],
            'attributes' => [
                'placeholder' => 'YYYY',
            ],
        ]);

        $fieldset->add([
            'name' => 'to',
            'type' => 'Text',
            'options' => [
                'label' => $this->translate('To year'),
            ],
            'attributes' => [
                'placeholder' => 'YYYY',
            ],
        ]);

        return $fieldset;
    }

    protected function itemSetFieldset()
    {
        $fieldset = new Fieldset('itemSet');

        $fieldset->add([
            'name' => 'ids',
            'type' => 'MultiCheckbox',
            'options' => [
                'label' => $this->translate('Collections'),
                'value_options' => $this->getItemSetsOptions(),
            ],
        ]);

        return $fieldset;
    }

    protected function textFieldset()
    {
        $fieldset = new Fieldset('text');

        $fieldset->add([
            'type' => 'Collection',
            'name' => 'filters',
            'options' => [
                'label' => 'Filters',
                'count' => 2,
                'should_create_template' => true,
                'allow_add' => true,
                'target_element' => $this->getFilterFieldset(),
            ],
        ]);

        $fieldset->add([
            'type' => 'Text',
            'name' => 'creation-year',
            'options' => [
                'label' => $this->translate('Creation year'),
            ],
            'attributes' => [
                'placeholder' => $this->translate('YYYY'),
            ],
        ]);

        return $fieldset;
    }

    protected function translate($string)
    {
        return $this->getTranslator()->translate($string);
    }

    protected function getItemSetsOptions()
    {
        $api = $this->getApiManager();

        $itemSets = $api->search('item_sets', [
            'is_public' => true,
        ])->getContent();
        $options = [];
        foreach ($itemSets as $itemSet) {
            $options[$itemSet->id()] = $itemSet->displayTitle();
        }

        return $options;
    }

    protected function getForm($name, $options)
    {
        $formElementManager = $this->getFormElementManager();
        return $formElementManager->get($name, $options);
    }

    protected function getFilterFieldset()
    {
        $options = $this->getOptions();
        return $this->getForm('PslSearchForm\Form\FilterFieldset', $options);
    }
}
