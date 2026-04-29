<?php

namespace BurnBright\ImportExport;

use SilverStripe\Dev\CSVParser;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\View\ViewableData;
/**
 * View the content of a given CSV file
 */
class CSVPreviewer extends ViewableData
{

    protected $headings;

    protected $rows;

    protected $previewcount = 5;

    public function __construct(protected $file)
    {
    }

    /**
     * Choose the nubmer of lines to preview
     */
    public function setPreviewCount($count)
    {
        $this->previewcount = $count;

        return $this;
    }

    /**
     * Extract preview of CSV from file
     */
    public function loadCSV()
    {
        $parser = new CSVParser($this->file);
        $count = 0;
        foreach ($parser as $row) {
            $this->rows[]= $row;
            $count++;
            if ($count == $this->previewcount) {
                break;
            }
        }

        $firstrow = array_keys($this->rows[0]);

        //hack to include first row as a
        array_unshift($this->rows, array_combine($firstrow, $firstrow));

        if (count($this->rows) > 0) {
            $this->headings = $firstrow;
        }
    }

    /**
     * Render the previewer
     * @return string
     */
    public function forTemplate()
    {
        if (!$this->rows) {
            $this->loadCSV();
        }

        return $this->renderWith("CSVPreviewer");
    }

    /**
     * Get the CSV headings for use in template
     * @return ArrayList
     */
    public function getHeadings()
    {
        if (!$this->headings) {
            return null;
        }

        $out = ArrayList::create();
        foreach ($this->headings as $heading) {
            $out->push(
                ArrayData::create([
                    "Label" => $heading
                ])
            );
        }

        return $out;
    }

    /**
     * Get CSV rows/cols for use in template
     * @return ArrayList
     */
    public function getRows()
    {
        $out = ArrayList::create();
        foreach ($this->rows as $row) {
            $columns = ArrayList::create();
            foreach ($row as $column => $value) {
                $columns->push(
                    ArrayData::create([
                        "Heading"=> $column,
                        "Value" => $value
                    ])
                );
            }

            $out->push(
                ArrayData::create([
                    "Columns" => $columns
                ])
            );
        }

        return $out;
    }
}
