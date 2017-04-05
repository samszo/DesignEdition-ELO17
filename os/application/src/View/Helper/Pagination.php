<?php
namespace Omeka\View\Helper;

use Omeka\Service\Paginator;
use Zend\View\Helper\AbstractHelper;

class Pagination extends AbstractHelper
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/pagination';

    /**
     * @var \Omeka\Service\Paginator
     */
    protected $paginator;

    /**
     * Name of view script, or a view model
     *
     * @var string|\Zend\View\Model\ModelInterface
     */
    protected $partialName;

    /**
     * Construct the helper.
     *
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * Configure the pagination.
     *
     * @param string|null $partialName Name of view script
     * @param int|null $totalCount The total record count
     * @param int|null $currentPage The current page number
     * @param int|null $perPage The number of records per page
     * @return self
     */
    public function __invoke($partialName = null, $totalCount = null, $currentPage = null,
        $perPage = null
    ) {
        if (null !== $totalCount) {
            $this->getPaginator()->setTotalCount($totalCount);
        }
        if (null !== $currentPage) {
            $this->getPaginator()->setCurrentPage($currentPage);
        }
        if (null !== $perPage) {
            $this->getPaginator()->setPerPage($perPage);
        }
        $this->partialName = $partialName ?: self::PARTIAL_NAME;
        return $this;
    }

    /**
     * Render the pagination markup.
     *
     * @return string
     */
    public function __toString()
    {
        $paginator = $this->getPaginator();

        // Page count
        $pageCount = $paginator->getPageCount();

        // Current page number cannot be more than page count
        if ($paginator->getCurrentPage() > $pageCount) {
            $paginator->setCurrentPage($pageCount);
        }

        return $this->getView()->partial(
            $this->partialName,
            [
                'totalCount'      => $paginator->getTotalCount(),
                'perPage'         => $paginator->getPerPage(),
                'currentPage'     => $paginator->getCurrentPage(),
                'previousPage'    => $paginator->getPreviousPage(),
                'nextPage'        => $paginator->getNextPage(),
                'pageCount'       => $pageCount,
                'query'           => $this->getView()->params()->fromQuery(),
                'firstPageUrl'    => $this->getUrl(1),
                'previousPageUrl' => $this->getUrl($paginator->getPreviousPage()),
                'nextPageUrl'     => $this->getUrl($paginator->getNextPage()),
                'lastPageUrl'     => $this->getUrl($pageCount),
                'pagelessUrl'     => $this->getPagelessUrl(),
                'offset'          => $paginator->getOffset()
            ]
        );
    }

    protected function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Get a pagination URL.
     *
     * @param int $page The page number
     * @return string
     */
    protected function getUrl($page)
    {
        $query = $this->getView()->params()->fromQuery();
        $query['page'] = (int) $page;
        return $this->getView()->url(null, [], ['query' => $query], true);
    }

    /**
     * Get a URL with the page parameter removed.
     *
     * Suitable for use as a form action URL.
     *
     * @return string
     */
    protected function getPagelessUrl()
    {
        $query = $this->getView()->params()->fromQuery();
        unset($query['page']);
        return $this->getView()->url(null, [], ['query' => $query], true);
    }
}
