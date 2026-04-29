<?php

namespace BurnBright\ImportExport\BulkLoader;

use SilverStripe\Dev\BulkLoader_Result;

/**
 * Store result information about a BulkLoader import.
 */
class BetterBulkLoader_Result extends BulkLoader_Result
{

    /**
     * Keep track of skipped records.
     * @var array
     */
    protected $skipped = [];

    /**
     * @return int
     */
    public function SkippedCount()
    {
        return count($this->skipped);
    }

    /**
     * @param string $message Reason for skipping
     */
    public function addSkipped($message = null)
    {
        $this->skipped[] = [
            'Message' => $message
        ];
    }

    /**
     * Get an array of messages describing the result.
     * @return array messages
     */
    public function getMessageList()
    {
        $output =  [];
        if ($this->CreatedCount()) {
            $output['created'] = _t(
                'BulkLoader.IMPORTEDRECORDS', "Imported {count} new records.",
                ['count' => $this->CreatedCount()]
            );
        }

        if ($this->UpdatedCount()) {
            $output['updated'] = _t(
                'BulkLoader.UPDATEDRECORDS', "Updated {count} records.",
                ['count' => $this->UpdatedCount()]
            );
        }

        if ($this->DeletedCount()) {
            $output['deleted'] =  _t(
                'BulkLoader.DELETEDRECORDS', "Deleted {count} records.",
                ['count' => $this->DeletedCount()]
            );
        }

        if ($this->SkippedCount()) {
            $output['skipped'] =  _t(
                'BulkLoader.SKIPPEDRECORDS', "Skipped {count} bad records.",
                ['count' => $this->SkippedCount()]
            );
        }

        if (!$this->CreatedCount() && !$this->UpdatedCount()) {
            $output['empty'] = _t('BulkLoader.NOIMPORT', "Nothing to import");
        }

        return $output;
    }

    /**
     * Genenrate a human-readable result message.
     * 
     * @return string
     */
    public function getMessage()
    {
        return implode("\n", $this->getMessageList());
    }

    /**
     * Provide a useful message type, based on result.
     * @return string
     */
    public function getMessageType()
    {
        $type = "bad";
        if ($this->Count() !== 0) {
            $type = "good";
        }

        if ($this->SkippedCount()) {
            $type= "warning";
        }

        return $type;
    }
}
