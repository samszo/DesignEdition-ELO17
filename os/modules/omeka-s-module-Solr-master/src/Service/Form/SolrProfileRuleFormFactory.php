<?php
namespace Solr\Service\Form;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Solr\Form\Admin\SolrProfileRuleForm;

class SolrProfileRuleFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $valueExtractorManager = $services->get('Solr\ValueExtractorManager');
        $valueFormatterManager = $services->get('Solr\ValueFormatterManager');
        $api = $services->get('Omeka\ApiManager');
        $translator = $services->get('MvcTranslator');

        $form = new SolrProfileRuleForm(null, $options);
        $form->setTranslator($translator);
        $form->setValueExtractorManager($valueExtractorManager);
        $form->setValueFormatterManager($valueFormatterManager);
        $form->setApiManager($api);

        return $form;
    }
}
