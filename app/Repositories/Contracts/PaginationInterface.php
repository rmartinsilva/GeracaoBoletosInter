<?php

namespace  App\Repositories\Contracts;

interface PaginationInterface{

    /*
    * @return <stdClass></stdClass>
    */
    public function items() : array;
    public function total() : int;
    public function from(): int;
    public function to(): int;
    public function isFirstPage() : bool;
    public function isLastPage() : bool;
    public function hasPages(): bool;
    public function currentPage() : int;
    public function getNumberNextPage() : int;
    public function getNumberPreviousPage() : int;


    
}