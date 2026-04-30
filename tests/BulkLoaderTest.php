<?php

use BurnBright\ImportExport\BulkLoader\BetterBulkLoader;
use BurnBright\ImportExport\BulkLoader\Sources\ArrayBulkLoaderSource;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

class BulkLoaderTest extends SapphireTest
{
    
    protected static $fixture_file = 'importexport/tests/fixtures/BulkLoaderTest.yaml';

    protected $extraDataObjects = [
        'BulkLoaderTest_Person',
        'BulkLoaderTest_Country'
    ];

    public function testLoading()
    {
        $loader = BetterBulkLoader::create("BulkLoaderTest_Person");

        $loader->columnMap = [
            "first name" => "FirstName",
            "last name" => "Surname",
            "name" => "Name",
            "age" => "Age",
            "country" => "Country.Code",
        ];

        $loader->transforms = [
            "Name" => [
                'callback' => function ($value, $obj) {
                    $name =  explode(" ", $value);
                    $obj->FirstName = $name[0];
                    $obj->Surname = $name[1];
                }
            ],
            "Country.Code" => [
                "link" => true, //link up to existing relations
                "create" => false //don't create new relation objects
            ]
        ];

        $loader->duplicateChecks = [
            "FirstName"
        ];

        //set the source data
        $data = [
            ["name" => "joe bloggs", "age" => "62", "country" => "NZ"],
            ["name" => "alice smith", "age" => "24", "country" => "AU"]
        ];
        $loader->setSource(new ArrayBulkLoaderSource($data));

        $results = $loader->load();
        $this->assertEquals($results->CreatedCount(), 2);
        $this->assertEquals($results->UpdatedCount(), 0);
        $this->assertEquals($results->DeletedCount(), 0);
        $this->assertEquals($results->SkippedCount(), 0);
        $this->assertEquals($results->Count(), 2);

        $joe = BulkLoaderTest_Person::get()->filter(["FirstName" => "joe"])
                ->first();

        $this->assertNotNull($joe, "joe has been created");
        $this->assertNotEquals($joe->CountryID, 0);
        //relation has been succesfully joined
        $this->assertEquals($joe->Country()->Title, "New Zealand");
        $this->assertEquals($joe->Country()->Code, "NZ");
    }

    public function testLoadUpdatesOnly()
    {
        //Set up some existing dataobjects
        $nz = BulkLoaderTest_Country::get()->find('Code','NZ');
        $au = BulkLoaderTest_Country::get()->find('Code','AU');

BulkLoaderTest_Person::create(["FirstName" => "joe", "Surname" => "Kiwi", "Age" => "62", "CountryID" => $nz->ID])->write();
       BulkLoaderTest_Person::create(["FirstName" => "bruce", "Surname" => "Aussie", "Age" => "24", "CountryID" => $au->ID])->write();

        $this->assertEquals(2,BulkLoaderTest_Person::get()->Count(), "Two people exist in BulkLoaderTest_Person class");
        
        $loader = BetterBulkLoader::create("BulkLoaderTest_Person");
        $loader->addNewRecords = false;  // don't add new records from source
        $loader->columnMap = [
            "firstname" => "FirstName",
            "surname" => "Surname",
            "age" => "Age",
            "country" => "Country.Code"
        ];
        $loader->transforms = [
            "Country.Code" => [
                "link" => true, //link up to existing relations
                "create" => false //don't create new relation objects
            ]
        ];
        $loader->duplicateChecks = [
            "FirstName"
        ];
        //set the source data.  Joe has aged one year and shifted to Australia.  Bruce has aged a year too, but is missing other elements, which should remain the same.
        $data = [
            ["firstname" => "joe", "surname" => "Kiwi", "age" => "63", "country" => "AU"],
            ["firstname" => "bruce", "age" => "25"],
            ["firstname" => "NotEntered", "surname" => "should not be entered", "age" => "33", "country" => "NZ"],
            ["firstname" => "NotEntered2", "surname" => "should not be entered as well", "age" => "24", "country" => "AU"]
        ];
        $loader->setSource(new ArrayBulkLoaderSource($data));
        
        $results = $loader->load();
        $this->assertEquals($results->CreatedCount(), 0);
        $this->assertEquals($results->UpdatedCount(), 2);
        $this->assertEquals($results->SkippedCount(), 2);
        $this->assertEquals($results->Count(), 2);
        
        $this->assertEquals(2, BulkLoaderTest_Person::get()->Count(), 'Should be two instances');
        $this->assertNull(BulkLoaderTest_Person::get()->find('FirstName', 'NotEntered'), 'New item "NotEntered" should not be added to BulkLoaderTest_Person');
        $this->assertNull(BulkLoaderTest_Person::get()->find('FirstName', 'NotEntered2'), 'New item "NotEntered2" should not be added to BulkLoaderTest_Person');

        $joe = BulkLoaderTest_Person::get()->find('FirstName', 'joe');
        $this->assertSame('63', $joe->Age, 'Joe should have the age of 63');
        $this->assertSame('Australia', $joe->Country()->Title, 'Joe should have the CountryID assigned to Australia');

        $bruce = BulkLoaderTest_Person::get()->find('FirstName', 'bruce');
        $this->assertSame('25', $bruce->Age, 'Bruce should have aged by one year to 25');
        $this->assertSame('Aussie', $bruce->Surname, 'Bruce should still have the surname of Aussie');
        $this->assertSame('Australia', $bruce->Country()->Title, 'Bruce should still have the CountryID assigned for Australia');
    }

    public function testColumnMap(): never
    {
        $this->markTestIncomplete("Implement this");
    }

    public function testTransformCallback()
    {
        $loader = BetterBulkLoader::create("BulkLoaderTest_Person");
        $data = [
            ["FirstName" => "joe", "age" => "62", "country" => "NZ"]
        ];
        $loader->setSource(new ArrayBulkLoaderSource($data));
        $loader->transforms = [
            'FirstName' => [
                'callback' => strtoupper(...)
            ]
        ];
        $results = $loader->load();
        $this->assertEquals($results->CreatedCount(), 1);
        $result = $results->Created()->first();
        $this->assertEquals("JOE", $result->FirstName, "First name has been transformed");
    }

    public function testRequiredFields()
    {
        $loader = BetterBulkLoader::create("BulkLoaderTest_Person");
        $data = [
            ["FirstName" => "joe", "Surname" => "Bloggs"], //valid
            ["FirstName" => 0, "Surname" => "Bloggs"], //invalid firstname
            ["FirstName" => null], //invalid firstname
            ["FirstName" => "", "Surname" => ""], //invalid firstname
            ["age" => "25", "Surname" => "Smith"], //invalid firstname
            ["FirstName" => "Jane"], //valid
        ];
        $loader->setSource(new ArrayBulkLoaderSource($data));
        $loader->transforms = [
            'FirstName' => [
                'required' => true
            ]
        ];
        $results = $loader->load();
        $this->assertEquals(2, $results->CreatedCount(), "Created 2");
        $this->assertEquals(4, $results->SkippedCount(), "Skipped 4");
    }
}

class BulkLoaderTest_Person extends DataObject implements TestOnly
{

    private static $table_name = 'BulkLoaderTest_Person';

    private static $db = [
        "FirstName" => "Varchar",
        "Surname" => "Varchar",
        "Age" => "Int"
    ];

    private static $has_one = [
        "Country" => "BulkLoaderTest_Country"
    ];
}

class BulkLoaderTest_Country extends Dataobject implements TestOnly
{

    private static $table_name = 'BulkLoaderTest_Country';

    private static $db = [
        "Title" => "Varchar",
        "Code" => "Varchar"
    ];
}
