<?php
namespace Omeka\Api\Representation;

class PropertyRepresentation extends AbstractVocabularyMemberRepresentation
{
    /**
     * {@inheritDoc}
     */
    public function getControllerName()
    {
        return 'property';
    }

    /**
     * {@inheritDoc}
     */
    public function getJsonLdType()
    {
        return 'o:Property';
    }

    /**
     * Get the resource count of this property.
     *
     * @return int
     */
    public function itemCount()
    {
        $response = $this->getServiceLocator()->get('Omeka\ApiManager')
            ->search('items', [
                'has_property' => [$this->id() => true],
                'limit' => 0,
            ]);
        return $response->getTotalResults();
    }
}
