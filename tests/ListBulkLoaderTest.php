<?php

use BurnBright\ImportExport\BulkLoader\ListBulkLoader;
use BurnBright\ImportExport\BulkLoader\Sources\ArrayBulkLoaderSource;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class ListBulkLoaderTest extends SapphireTest
{

    protected $extraDataObjects = [
        'ListBulkLoaderTest_Person'
    ];

    public function testImport()
    {
        $parent = \ListBulkLoaderTest_Person::create(["Name" => "George", "Age" => 55]);
        $parent->write();

        //add one existing child
        $existingchild = \ListBulkLoaderTest_Person::create(["Name" => "Xavier", "Age" => 13]);
        $existingchild->write();
        $parent->Children()->add($existingchild);

        $loader = ListBulkLoader::create($parent->Children());
        $loader->duplicateChecks = [
            "Name"
        ];

        $source = new ArrayBulkLoaderSource([
            [], //skip record
            ["Name" => "Martha", "Age" => 1], //new record
            ["Name" => "Xavier", "Age" => 16], //update record
            ["Name" => "Joanna", "Age" => 3], //new record
            "" //skip record
        ]);
        $loader->setSource($source);
        $result = $loader->load();
        $this->assertEquals(2, $result->SkippedCount(), "Records skipped");
        $this->assertEquals(2, $result->CreatedCount(), "Records created");
        $this->assertEquals(1, $result->UpdatedCount(), "Record updated");
        $this->assertEquals(3, $result->Count(), "Records imported");
        $this->assertEquals(4, ListBulkLoaderTest_Person::get()->count(), "Total DataObjects is now 4");
        $this->assertEquals(3, $parent->Children()->count(), "Parent has 3 children");
    }

    public function testDeleteExisting(): never
    {
        $this->markTestIncomplete("test deletion");

        //data list should be emptied
        //should not delete unrelated records
    }
}

class ListBulkLoaderTest_Person extends DataObject implements TestOnly
{

    private static $table_name = 'ListBulkLoaderTest_Person';

    private static $db = [
        "Name" => "Varchar",
        "Age" => "Int"
    ];

    private static $has_one = [
        "Parent" => "ListBulkLoaderTest_Person"
    ];

    private static $has_many = [
        "Children" => "ListBulkLoaderTest_Person"
    ];
}
