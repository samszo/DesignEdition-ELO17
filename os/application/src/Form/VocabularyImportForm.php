<?php
namespace Omeka\Form;

use Zend\Form\Form;

class VocabularyImportForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'file',
            'type' => 'file',
            'options' => [
                'label' => 'Vocabulary File', // @translate
                'info' => 'Accepts the following formats: RDF/XML, RDF/JSON, N-Triples, and Turtle. See the Vocabulary Import Documentation for details.', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:prefix',
            'type' => 'text',
            'options' => [
                'label' => 'Prefix', // @translate
                'info' => 'A concise vocabulary identifier, used as a shorthand proxy for the namespace URI.', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:namespace_uri',
            'type' => 'text',
            'options' => [
                'label' => 'Namespace URI', // @translate
                'info' => 'The unique namespace URI used by the vocabulary to identify local member classes and properties.', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:label',
            'type' => 'text',
            'options' => [
                'label' => 'Label', // @translate
                'info' => 'A human-readable title of the vocabulary.', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);

        $this->add([
            'name' => 'o:comment',
            'type' => 'textarea',
            'options' => [
                'label' => 'Comment', // @translate
                'info' => 'A human-readable description of the vocabulary.', // @translate
            ],
        ]);
    }
}
