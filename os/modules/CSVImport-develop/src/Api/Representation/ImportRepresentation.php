<?php
namespace CSVImport\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class ImportRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLd()
    {
        $undo_job = null;
        if($this->undoJob()) {
            $undo_job = $this->undoJob()->getReference();
        }

        return [
            'added_count'    => $this->addedCount(),
            'updated_count'  => $this->updatedCount(),
            'comment'        => $this->comment(),
            'resource_type'  => $this->resourceType(),
            'o:job'          => $this->job()->getReference(),
            'o:undo_job'     => $undo_job
        ];
    }
    
    public function getJsonLdType()
    {
        return 'o:CSVimportImport';
    }

    public function job()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getJob());
    }

    public function undoJob()
    {
        return $this->getAdapter('jobs')
            ->getRepresentation($this->resource->getUndoJob());
    }

    public function comment()
    {
        return $this->resource->getComment();
    }

    public function addedCount()
    {
        return $this->resource->getAddedCount();
    }

    public function resourceType()
    {
        return $this->resource->getResourceType();
    }
    
    public function hasErr()
    {
        return $this->resource->getHasErr();
    }
}
