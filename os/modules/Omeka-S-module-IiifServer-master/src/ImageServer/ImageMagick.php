<?php

/*
 * Copyright 2015-2017 Daniel Berthereau
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

namespace IiifServer\ImageServer;

use Exception;
use Omeka\File\Manager as FileManager;
use Omeka\Service\Cli;
use IiifServer\AbstractImageServer;

/**
 * Helper to create an image from another one with IIIF arguments.
 *
 * @package IiifServer
 */
class ImageMagick extends AbstractImageServer
{
    /**
     * Path to the ImageMagick "convert" command.
     *
     * @var string
     */
    protected $convertPath;

    // List of managed IIIF media types.
    protected $_supportedFormats = [
        'image/jpeg' => 'JPG',
        'image/png' => 'PNG',
        'image/tiff' => 'TIFF',
        'image/gif' => 'GIF',
        'application/pdf' => 'PDF',
        'image/jp2' => 'JP2',
        'image/webp' => 'WEBP',
    ];

    protected $fileManager;
    protected $cli;
    protected $convertDir;

    /**
     * List of the fetched images in order to remove them after process.
     *
     * @var array
     */
    protected $fetched = [];

    /**
     * Check for the php extension.
     *
     * @throws Exception
     */
    public function __construct(FileManager $fileManager, $commandLineArgs)
    {
        $this->fileManager = $fileManager;
        $this->cli = $commandLineArgs['cli'];
        $this->convertPath = $commandLineArgs['convertPath'];

        $t = $this->getTranslator();

        // TODO Get the true list of supported formats.
        // $this->_supportedFormats = array_intersect($this->_supportedFormats, \Imagick::queryFormats());
    }

    /**
     * Transform an image into another image according to params.
     *
     * @internal The args are currently already checked in the controller.
     *
     * @param array $args List of arguments for the transformation.
     * @return string|null The filepath to the temp image if success.
     */
    public function transform(array $args = [])
    {
        if (empty($args)) {
            return;
        }

        $this->_args = $args;
        $args = &$this->_args;

        if (!$this->checkMediaType($args['source']['media_type'])
                || !$this->checkMediaType($args['format']['feature'])
            ) {
            return;
        }

        $image = $this->_loadImageResource($args['source']['filepath']);
        if (empty($image)) {
            return;
        }

        // Get width and height if missing.
        if (empty($args['source']['width']) || empty($args['source']['height'])) {
            list($args['source']['width'], $args['source']['height']) = getimagesize($image);
        }

        // Region + Size.
        $extraction = $this->_prepareExtraction();
        if (!$extraction) {
            $this->_destroyIfFetched($image);
            return;
        }

        list(
            $sourceX,
            $sourceY,
            $sourceWidth,
            $sourceHeight,
            $destinationWidth,
            $destinationHeight) = $extraction;

        $params = [];
        // The background is normally useless, but it's costless.
        $params[] = '-background black';
        $params[] = '+repage';
        $params[] = '-flatten';
        $params[] = '-page ' . escapeshellarg(sprintf('%sx%s+0+0', $sourceWidth, $sourceHeight));
        $params[] = '-crop ' . escapeshellarg(sprintf('%dx%d+%d+%d', $sourceWidth, $sourceHeight, $sourceX, $sourceY));
        $params[] = '-thumbnail ' . escapeshellarg(sprintf('%sx%s', $destinationWidth, $destinationHeight));
        $params[] = '-page ' . escapeshellarg(sprintf('%sx%s+0+0', $destinationWidth, $destinationHeight));

        // Mirror.
        switch ($args['mirror']['feature']) {
            case 'mirror':
            case 'horizontal':
                $params[] = '-flop';
                break;

            case 'vertical':
                $params[] = '-flip';
                break;

            case 'both':
                $params[] = '-flop';
                $params[] = '-flip';
                break;

            case 'default':
                // Nothing to do.
                break;

            default:
                $this->_destroyIfFetched($image);
                return;
        }

        // Rotation.
        switch ($args['rotation']['feature']) {
            case 'noRotation':
                break;

            case 'rotationBy90s':
            case 'rotationArbitrary':
                $params[] = '-rotate ' . escapeshellarg($args['rotation']['degrees']);
                break;

            default:
                $this->_destroyIfFetched($image);
                return;
        }

        // Quality.
        switch ($args['quality']['feature']) {
            case 'default':
                break;

            case 'color':
                // No change, because only one image is managed.
                break;

            case 'gray':
                $params[] = '-colorspace Gray';
                break;

            case 'bitonal':
                $params[] = '-monochrome';
                break;

            default:
                $this->_destroyIfFetched($image);
                return;
        }

        // Save resulted resource into the specified format.
        $extension = strtolower($this->_supportedFormats[$args['format']['feature']]);
        $file = $this->fileManager->getTempFile();
        $destination = $file->getTempPath() . '.' . $extension;
        $file->delete();

        $command = sprintf(
            '%s %s %s %s',
            $this->convertPath,
            escapeshellarg($image . '[0]'),
            implode(' ', $params),
            escapeshellarg($this->_supportedFormats[$args['format']['feature']] . ':' . $destination)
        );

        $result = $this->cli->execute($command);

        $this->_destroyIfFetched($image);

        return $result !== false ? $destination : null;
    }

    /**
     * Load an image from anywhere.
     *
     * @param string $source Path of the managed image file
     * @return false|string
     */
    protected function _loadImageResource($source)
    {
        if (empty($source)) {
            return false;
        }

        try {
            // The source can be a local file or an external one.
            $store = $this->fileManager->getStore();
            if (get_class($store) == Omeka\File\Store\LocalStore::class) {
                if (!is_readable($source)) {
                    return false;
                }
                $image = $source;
            }
            // When the storage is external, the file should be fetched before.
            else {
                $file = $this->fileManager->getTempFile();
                $tempPath = $file->getTempPath();
                $file->delete();
                $result = copy($source, $tempPath);
                if (!$result) {
                    return false;
                }
                $this->fetched[$tempPath] = true;
                $image = $tempPath;
            }
        } catch (Exception $e) {
            $logger = $this->getLogger();
            $t = $this->getTranslator();
            $logger->log(Logger::ERR, sprintf($t->translate("ImageMagick failed to open the file \"%s\". Details:\n%s"), $source, $e->getMessage()));
            return false;
        }

        return $image;
    }

    /**
     * Destroy an image if fetched.
     *
     * @param string $image
     */
    protected function _destroyIfFetched($image)
    {
        if (isset($this->fetched[$image])) {
            unlink($image);
            unset($this->fetched[$image]);
        }
    }
}
