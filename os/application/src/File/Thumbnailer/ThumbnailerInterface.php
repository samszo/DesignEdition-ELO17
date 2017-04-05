<?php
namespace Omeka\File\Thumbnailer;

use Omeka\File\File;
use Omeka\File\Manager;

interface ThumbnailerInterface
{
    /**
     * Set the file source.
     *
     * @param File $source
     */
    public function setSource(File $source);

    /**
     * Set options for all thumbnail strategies.
     *
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * Create a thumbnail derivative.
     *
     * Implementations should attempt to copy the source file, convert it to
     * JPEG, and resize it according to the passed strategy and constraint. They
     * should handle at least the "default" and "square" thumbnail strategies.
     *
     * @param Manager $fileManager
     * @param string $strategy Creation strategy (default is "default")
     * @param int $constraint Constraint for this strategy
     * @param array $options Options for this strategy
     * @return string Path to temporary thumbnail file
     */
    public function create(Manager $fileManager, $strategy, $constraint, array $options = []);
}
