<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\ActivityDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadActivityDocumentAction
{
    /**
     * Upload and version a document for an activity.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Activity $activity, array $data, UploadedFile $file): ActivityDocument
    {
        return DB::transaction(function () use ($activity, $data, $file) {
            $path = (new ActivityDocument)->uploadFile($file, 'activity-documents');

            $parentId = $data['parent_id'] ?? null;
            $version = 1;

            if ($parentId) {
                /** @var ActivityDocument $parent */
                $parent = ActivityDocument::findOrFail($parentId);
                if ($parent->activity_id !== $activity->id) {
                    abort(403, 'Parent document does not belong to this activity.');
                }
                $rootParentId = $parent->parent_id ?: $parent->id;
                $parentId = $rootParentId;
                $maxVersion = ActivityDocument::where('parent_id', $rootParentId)
                    ->orWhere('id', $rootParentId)
                    ->max('version');
                $version = $maxVersion + 1;
            } else {
                $existingRootDoc = ActivityDocument::where('activity_id', $activity->id)
                    ->whereNull('parent_id')
                    ->where('file_name', $file->getClientOriginalName())
                    ->first();

                if ($existingRootDoc) {
                    $parentId = $existingRootDoc->id;
                    $maxVersion = ActivityDocument::where('parent_id', $parentId)
                        ->orWhere('id', $parentId)
                        ->max('version');
                    $version = $maxVersion + 1;
                }
            }

            return ActivityDocument::create([
                'activity_id' => $activity->id,
                'parent_id' => $parentId,
                'version' => $version,
                'uploaded_by' => Auth::id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'file_size' => $file->getSize(),
                'description' => $data['description'] ?? null,
            ]);
        });
    }
}
