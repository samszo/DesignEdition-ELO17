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

namespace Solr\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class SolrProfileRule extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Solr\Entity\SolrProfile")
     * @JoinColumn(nullable=false)
     */
    protected $solrProfile;

    /**
     * @ManyToOne(targetEntity="Solr\Entity\SolrField")
     * @JoinColumn(nullable=false)
     */
    protected $solrField;

    /**
     * @Column(type="string", length=255)
     */
    protected $source;

    /**
     * @Column(type="json_array")
     */
    protected $settings;

    public function getId()
    {
        return $this->id;
    }

    public function setSolrProfile(SolrProfile $solrProfile)
    {
        $this->solrProfile = $solrProfile;
    }

    public function getSolrProfile()
    {
        return $this->solrProfile;
    }

    public function setSolrField(SolrField $solrField)
    {
        $this->solrField = $solrField;
    }

    public function getSolrField()
    {
        return $this->solrField;
    }

    public function setSource($source)
    {
        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        return $this->settings;
    }
}