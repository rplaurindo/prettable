<?php

namespace PreTTable\Helpers;

class Pagination {
    
    private $collection;
    
    private $limit;
    
    private $count;
    
    private $totalPages;
    
    private $currentPageNumber;
    
//     a array with items of page
    private $page;
    
    function __construct(array $collection, $limit) {
        $this->collection = $collection;
        $this->count = count($this->collection);
        $this->limit = self::resolvesLimit($this->count, $limit);
        $this->totalPages = self::calculatesTotalPages($this->count, $limit);
        $this->page = [];
    }
    
    function getPage($number) {
        $this->resolvesPage($number);
        return $this->page;
    }
    
    function getTotalPages() {
        return $this->totalPages;
    }
    
    private function resolvesPage($pageNumber) {
        if (!$this->currentPageNumber
            || $pageNumber != $this->currentPageNumber
            ) {
                $this->currentPageNumber = self::
                resolvesPageNumber($pageNumber, $this->totalPages);
                
                $offset = self::calculatesOffset($this->limit,
                    $this->currentPageNumber,
                    $this->count);
                
                $this->page = array_slice($this->collection, $offset,
                    $this->limit);
            }
    }
    
    static function calculatesOffset($limit, $pageNumber = 1, $count = null) {
        if (isset($count)) {
            $limit = self::resolvesLimit($count, $limit);
            $totalPages = self::calculatesTotalPages($count, $limit);
//             cause' of these filters isn't possible the offset exceed maximum
            $pageNumber = self::resolvesPageNumber($pageNumber, $totalPages);
        } else if ($pageNumber < 1) {
            $pageNumber = 1;
        }
        
        $offset = $limit * ($pageNumber - 1);
        
        if ($offset < 0) {
            $offset = 0;
        }
        
        return $offset;
    }
    
    static function calculatesTotalPages($count, $limit) {
        $limit = self::resolvesLimit($count, $limit);
        
        if ($count > 0 && $limit > 0) {
            return ceil($count / $limit);
        }
        
        return 1;
    }
    
    private static function resolvesLimit($count, $limit) {
        if ($count === 0) {
            return $count;
        } else if ($limit < 0) {
            return 0;
        }
        
        return $limit;
    }
    
    private static function resolvesPageNumber($pageNumber, $totalPages) {
        if ($pageNumber > $totalPages) {
            return $totalPages;
        } else if ($pageNumber < 1) {
            return 1;
        }
        
        return $pageNumber;
    }
    
}
