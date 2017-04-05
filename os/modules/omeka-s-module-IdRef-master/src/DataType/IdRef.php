<?php
namespace IdRef\DataType;

use Zend\View\Renderer\PhpRenderer;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Representation\ValueRepresentation;
use Omeka\DataType\AbstractDataType;
use Omeka\Entity\Value;

class IdRef extends AbstractDataType
{
    public function getName()
    {
        return 'idref';
    }

    public function getLabel()
    {
        return 'IdRef';
    }

    public function form(PhpRenderer $view)
    {
        return $view->partial('idref/data-type/idref');
    }

    public function isValid(array $valueObject)
    {
        if (isset($valueObject['@value'])
            && is_string($valueObject['@value'])
            && '' !== trim($valueObject['@value'])
        ) {
            return true;
        }
        return false;
    }

    public function hydrate(array $valueObject, Value $value, AbstractEntityAdapter $adapter)
    {
        $value->setValue($valueObject['@value']);
        $value->setLang(null); // set default
        $value->setUri(null); // set default
        $value->setValueResource(null); // set default
    }

    public function render(PhpRenderer $view, ValueRepresentation $value)
    {
        return nl2br($view->escapeHtml($value->value()));
    }

    public function getJsonLd(ValueRepresentation $value)
    {
        $jsonLd = ['@value' => $value->value()];
        return $jsonLd;
    }
}
