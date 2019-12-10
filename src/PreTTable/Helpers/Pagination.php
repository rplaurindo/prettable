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
    
    function __construct(array $collection, $limit = null) {
        $this->collection = $collection;
        $this->count = count($this->collection);
        $this->limit = self::resolvesLimit($this->count, $limit);
        $this->totalPages = self::calculatesTotalPages($this->count, $limit);
        $this->page = [];
    }
    
    function getPage($number) {
        if (isset($this->limit)) {
            if (!$this->currentPageNumber
                || $number != $this->currentPageNumber
                ) {
                $this->currentPageNumber = self::resolvesPageNumber($number, $this->totalPages);
                
                $offset = self::calculatesOffset($this->limit,
                    $this->currentPageNumber,
                    $this->count);
                
                $this->page = array_slice($this->collection, $offset,
                    $this->limit);
            }
        } else {
            $this->page = $this->collection;
        }
        
        return $this->page;
    }
    
    function getTotalPages() {
        return $this->totalPages;
    }
    
    static function calculatesTotalPages($count, $limit = null) {
        $limit = self::resolvesLimit($count, $limit);
        
        if ($limit > 0 && $count > 0) {
            return ceil($count / $limit);
        }
        
        return 1;
    }
    
    static function calculatesOffset($count, $limit = 0, $pageNumber = 1) {
        if (isset($count)) {
            $limit = self::resolvesLimit($count, $limit);
            $totalPages = self::calculatesTotalPages($count, $limit);
//             cause' of these filters isn't possible the offset exceed maximum
            $pageNumber = self::resolvesPageNumber($pageNumber, $totalPages);
        } else if ($pageNumber <= 1) {
            $pageNumber = 1;
        }
        
        $offset = $limit * ($pageNumber - 1);
        
        if ($offset <= 0) {
            $offset = 0;
        }
        
        return $offset;
    }
    
    private static function resolvesLimit($count, $limit) {
        if ($count === 0) {
            return $count;
        } else if ($limit <= 0) {
            return 0;
        }
        
        return $limit;
    }
    
    private static function resolvesPageNumber($pageNumber, $totalPages) {
        if ($pageNumber > $totalPages) {
            return $totalPages;
        } else if ($pageNumber <= 1) {
            return 1;
        }
        
        return $pageNumber;
    }
    
}
