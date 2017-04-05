<?php

/*
 * Copyright 2015-2017 Daniel Berthereau
 * Copyright 2016-2017 BibLibre
 *
 * This software is governed by the CeCILL license under French law and abiding
 * by the rules of distribution of free software. You can use, modify and/or
 * redistribute the software under the terms of the CeCILL license as circulated
 * by CEA, CNRS and INRIA at the following URL "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and rights to copy, modify
 * and redistribute granted by the license, users are provided only with a
 * limited warranty and the software’s author, the holder of the economic
 * rights, and the successive licensors have only limited liability.
 *
 * In this respect, the user’s attention is drawn to the risks associated with
 * loading, using, modifying and/or developing or reproducing the software by
 * the user in light of its specific status of free software, that may mean that
 * it is complicated to manipulate, and that also therefore means that it is
 * reserved for developers and experienced professionals having in-depth
 * computer knowledge. Users are therefore encouraged to load and test the
 * software’s suitability as regards their requirements in conditions enabling
 * the security of their systems and/or data to be ensured and, more generally,
 * to use and operate it in the same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL license and that you accept its terms.
 */

namespace UniversalViewer\View\Helper;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Omeka\Module\Manager as ModuleManager;
use Zend\View\Helper\AbstractHelper;

class UniversalViewer extends AbstractHelper
{

    /**
     * Get the Universal Viewer for the provided resource.
     *
     * Proxies to {@link render()}.
     *
     * @param AbstractResourceEntityRepresentation|array $resource
     * @param array $options Associative array of optional values:
     *   - (string) class
     *   - (string) locale
     *   - (string) style
     *   - (string) config
     * @return string. The html string corresponding to the UniversalViewer.
     */
    public function __invoke($resource, $options = array())
    {
        static $iiifServerIsActive;

        if (empty($resource)) {
            return '';
        }

        // If the manifest is not provided in metadata, point to the manifest
        // created from Omeka files only when the Iiif Server is installed.
        if (is_null($iiifServerIsActive)) {
            $iiifServerIsActive = $this->moduleIsActive('IiifServer');
        }

        // Prepare the url of the manifest for a dynamic collection.
        if (is_array($resource)) {
            if (!$iiifServerIsActive) {
                return '';
            }

            $identifier = $this->buildIdentifierForList($resource);
            $route = 'iiifserver_presentation_collection_list';
            $urlManifest = $this->view->url(
                $route,
                ['id' => $identifier],
                ['force_canonical' => true]
            );
            $urlManifest = $this->view->iiifForceHttpsIfRequired($urlManifest);
            return $this->render($urlManifest, $options);
        }

        // Prepare the url for the manifest of a record after additional checks.
        $resourceName = $resource->resourceName();
        if (!in_array($resourceName, array('items', 'item_sets'))) {
            return '';
        }

        // Determine the url of the manifest from a field in the metadata.
        $urlManifest = '';
        $manifestProperty = $this->view->setting('universalviewer_manifest_property');
        if ($manifestProperty) {
            $urlManifest = $resource->value($manifestProperty);
            if ($urlManifest) {
                // Manage the case where the url is saved as an uri or a text.
                $urlManifest = $urlManifest->uri() ?: $urlManifest->value();
                return $this->render($urlManifest, $options);
            }
        }

        // If the manifest is not provided in metadata, point to the manifest
        // created from Omeka files if Iiif Server is installed.
        if (!$this->moduleIsActive('IiifServer')) {
            return '';
        }

        // Some specific checks.
        switch ($resourceName) {
            case 'items':
                // Currently, an item without files is unprocessable.
                if (count($resource->media()) == 0) {
                    // return $this->view->translate('This item has no files and is not displayable.');
                    return '';
                }
                $route = 'iiifserver_presentation_item';
                break;
            case 'item_sets':
                if ($resource->itemCount() == 0) {
                    // return $this->view->translate('This collection has no item and is not displayable.');
                    return '';
                }
                $route = 'iiifserver_presentation_collection';
                break;
        }

        $urlManifest = $this->view->url($route,
            ['id' => $resource->id()],
            ['force_canonical' => true]
        );
        $urlManifest = $this->view->iiifForceHttpsIfRequired($urlManifest);

        return $this->render($urlManifest, $options);
    }

    /**
     * Helper to create an identifier from a list of records.
     *
     * The dynamic identifier is a flat list of ids: "5,1,2,3".
     * If there is only one id, a comma is added to avoid to have the same route
     * than the collection itself.
     * In all cases the order of records is kept.
     *
     * @todo Use IiifServer\View\Helper\IiifCollectionList::buildIdentifierForList()
     *
     * @param array $resources
     * @return string
     */
    protected function buildIdentifierForList($resources)
    {
        $identifiers = array();
        foreach ($resources as $resource) {
            $identifiers[] = $resource->id();
        }

        $identifier = implode(',', $identifiers);

        if (count($identifiers) == 1) {
            $identifier .= ',';
        }

        return $identifier;
    }

    /**
     * Render a universal viewer for a url, according to options.
     *
     * @param string $urlManifest
     * @param array $options
     * @return string
     */
    protected function render($urlManifest, $options = array())
    {
        $class = isset($options['class'])
            ? $options['class']
            : $this->view->setting('universalviewer_class');
        if (!empty($class)) {
            $class = ' ' . $class;
        }

        $locale = isset($options['locale'])
            ? $options['locale']
            : $this->view->setting('universalviewer_locale');
        if (!empty($locale)) {
            $locale = ' data-locale="' . $locale . '"';
        }

        $style = isset($options['style'])
            ? $options['style']
            : $this->view->setting('universalviewer_style');
        if (!empty($style)) {
            $style = ' style="' . $style . '"';
        }

        // Default configuration file.
        $config = empty($args['config'])
            ? $this->view->basePath('/modules/UniversalViewer/view/public/universal-viewer/config.json')
            : $args['config'];
        $urlJs = $this->view->assetUrl('js/uv/lib/embed.js', 'UniversalViewer');

        $html = sprintf('<div class="uv%s" data-config="%s" data-uri="%s"%s%s></div>',
            $class,
            $config,
            $urlManifest,
            $locale,
            $style);
        $html .= sprintf('<script type="text/javascript" id="embedUV" src="%s"></script>', $urlJs);
        $html .= '<script type="text/javascript">/* wordpress fix */</script>';
        return $html;
    }

    /**
     * Helper to check if the IiifServer is installed.
     *
     * @param string $name
     * @return boolean
     */
    protected function moduleIsActive($name)
    {
        $serviceLocator = @$this->getView()->getHelperPluginManager()->getServiceLocator();
        $moduleManager = $serviceLocator->get('Omeka\ModuleManager');
        $module = $moduleManager->getModule($name);
        $moduIeIsActive = $module && $module->getState() == ModuleManager::STATE_ACTIVE;
        return $moduIeIsActive;
    }
}
