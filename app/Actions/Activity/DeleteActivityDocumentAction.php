<?php

namespace App\Actions\Activity;

use App\Models\ActivityDocument;
use Illuminate\Support\Facades\DB;

class DeleteActivityDocumentAction
{
    /**
     * Delete a document and its physical file.
     */
    public function execute(ActivityDocument $document): ?bool
    {
        return DB::transaction(function () use ($document) {
            $document->deleteFile($document->file_path);

            return $document->delete();
        });
    }
}
