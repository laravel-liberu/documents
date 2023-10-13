<?php

namespace LaravelLiberu\Documents\Traits;

use Illuminate\Support\Facades\Config;
use LaravelLiberu\Documents\Exceptions\DocumentConflict;
use LaravelLiberu\Documents\Models\Document;

trait Documentable
{
    public static function bootDocumentable()
    {
        self::deleting(fn ($model) => $model->attemptDocumentableDeletion());

        self::deleted(fn ($model) => $model->cascadeDocumentDeletion());
    }

    public function document()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    private function attemptDocumentableDeletion()
    {
        $shouldRestrict = Config::get('enso.documents.onDelete') === 'restrict'
            && $this->documents()->exists();

        if ($shouldRestrict) {
            throw DocumentConflict::delete();
        }
    }

    private function cascadeDocumentDeletion()
    {
        if (Config::get('enso.documents.onDelete') === 'cascade') {
            $this->documents()->delete();
        }
    }
}
