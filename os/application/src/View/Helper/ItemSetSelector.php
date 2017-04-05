<?php
namespace Omeka\View\Helper;

use Zend\View\Helper\AbstractHelper;

class ItemSetSelector extends AbstractHelper
{
    /**
     * Return the item set selector form control.
     *
     * @return string
     */
    public function __invoke()
    {
        $query = ['is_open' => true];
        $response = $this->getView()->api()->search('item_sets', $query);
        if ($response->isError()) {
            return;
        }

        // Organize items sets by owner.
        $itemSetOwners = [];
        foreach ($response->getContent() as $itemSet) {
            $owner = $itemSet->owner();
            $email = $owner ? $owner->email() : null;
            $itemSetOwners[$email]['owner'] = $owner;
            $itemSetOwners[$email]['item_sets'][] = $itemSet;
        }
        ksort($itemSetOwners);

        return $this->getView()->partial(
            'common/item-set-selector',
            [
                'itemSetOwners' => $itemSetOwners,
                'totalItemSetCount' => $response->getTotalResults(),
            ]
        );
    }
}
