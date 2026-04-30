<?php

declare(strict_types=1);

use BurnBright\ImportExport\BulkLoader\Sources\ArrayBulkLoaderSource;
use SilverStripe\Dev\SapphireTest;

class ArrayBulkLoaderSourceTest extends SapphireTest
{

    public function testIterator()
    {
        $data = [
            ["First" => 1],
            ["First" => 2]
        ];
        $source = new ArrayBulkLoaderSource($data);
        $iterator = $source->getIterator();
        $count = 0;
        foreach ($iterator as $record) {
            $this->assertEquals($data[$count], $record);
            $count++;
        }
    }
}
