<?php

use BurnBright\ImportExport\BulkLoader\Sources\CsvBulkLoaderSource;
use SilverStripe\Dev\SapphireTest;

class CsvBulkLoaderSourceTest extends SapphireTest
{

    public function testConfiguration()
    {
        $source = new CsvBulkLoaderSource();
        $source->setFilePath("asdf.csv")
            ->setFieldDelimiter("|")
            ->setFieldEnclosure(":")
            ->setHasHeader(false);
        $this->assertEquals("asdf.csv", $source->getFilePath());
        $this->assertEquals("|", $source->getFieldDelimiter());
        $this->assertEquals(":", $source->getFieldEnclosure());
        $this->assertEquals(false, $source->getHasHeader());
    }

    public function testNoHeaderFile()
    {
        $source = new CsvBulkLoaderSource();
        $source->setFilePath(__DIR__."/fixtures/Players.csv")
            ->setHasHeader(false);

        $rowassertions = [
            ["John", "He's a good guy", "ignored", "31/01/1988", "1"],
            ["Jane", "She is awesome.\\nSo awesome that she gets multiple rows and \\\"escaped\\\" strings in her biography", "ignored", "31/01/1982", "0"],
            ["Jamie","Pretty old\, with an escaped comma","ignored","31/01/1882","1"],
            ["Järg","Unicode FTW","ignored","31/06/1982","1"],
            //empty rows are skipped by default
            ["","nobio missing data","ignored"]
        ];

        $iterator = $source->getIterator();
        $count = 0;
        foreach ($iterator as $record) {
            $this->assertEquals(
                $rowassertions[$count],
                $record,
                sprintf('Row %d is valid', $count)
            );
            $count++;
        }
    }

    /**
     * @group testme
     */
    public function testWithHeaderFile()
    {
        $source = new CsvBulkLoaderSource();
        $source->setFilePath(__DIR__."/fixtures/Players_WithHeader.csv")
            ->setHasHeader(true);

        $rowassertions = [
            ["FirstName"=>"John", "Biography"=>"He's a good guy", "Ignore"=>"ignored", "Birthday"=>"31/01/1988", "IsRegistered"=>"1"],
            ["FirstName"=>"Jane", "Biography"=>"She is awesome.\\nSo awesome that she gets multiple rows and \\\"escaped\\\" strings in her biography", "Ignore"=>"ignored", "Birthday"=>"31/01/1982", "IsRegistered"=>"0"],
            ["FirstName"=>"Jamie","Biography"=>"Pretty old\, with an escaped comma","Ignore"=>"ignored","Birthday"=>"31/01/1882","IsRegistered"=>"1"],
            ["FirstName"=>"Järg","Biography"=>"Unicode FTW","Ignore"=>"ignored","Birthday"=>"31/06/1982","IsRegistered"=>"1"],
            //empty rows are skipped by default
            ["FirstName"=>"","Biography"=>"nobio missing data","Ignore"=>"ignored"]
        ];

        $iterator = $source->getIterator();
        $count = 0;
        foreach ($iterator as $record) {
            $this->assertEquals(
                $rowassertions[$count],
                $record,
                sprintf('Row %d is valid', $count)
            );
            $count++;
        }
        
        //assert header is correct
        $this->assertEquals(
            $source->getFirstRow(),
            ["FirstName", "Biography", "Ignore", "Birthday", "IsRegistered"]
        );
    }
}
