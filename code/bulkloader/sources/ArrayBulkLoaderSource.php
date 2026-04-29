<?php

namespace BurnBright\ImportExport\BulkLoader\Sources;

use BurnBright\ImportExport\BulkLoader\Sources\BulkLoaderSource;

/**
 * Array Bulk Loader Source
 * Useful for testing bulk loader. The output is the same as input.
 */
class ArrayBulkLoaderSource extends BulkLoaderSource
{

    public function __construct(protected $data)
    {
    }

    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }
}
