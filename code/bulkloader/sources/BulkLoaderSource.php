<?php

namespace BurnBright\ImportExport\BulkLoader\Sources;

use IteratorAggregate;

/**
 * An abstract source to bulk load records from.
 * Provides an iterator for retrieving records from.
 * 
 * Useful for holiding source configuration state.
 */
abstract class BulkLoaderSource implements IteratorAggregate
{

    /**
     * Provide iterator for bulk loading from.
     * Records are expected to be 1 dimensional key-value arrays.
     * @return Iterator
     */
    #[\ReturnTypeWillChange]
    abstract public function getIterator();
}
