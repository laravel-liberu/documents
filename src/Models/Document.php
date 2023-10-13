<?php

namespace LaravelLiberu\Documents\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use LaravelLiberu\Documents\Contracts\Ocrable;
use LaravelLiberu\Documents\Jobs\Ocr as Job;
use LaravelLiberu\Files\Contracts\Attachable;
use LaravelLiberu\Files\Contracts\CascadesFileDeletion;
use LaravelLiberu\Files\Contracts\OptimizesImages;
use LaravelLiberu\Files\Contracts\ResizesImages;
use LaravelLiberu\Files\Models\File;
use LaravelLiberu\Helpers\Traits\UpdatesOnTouch;

class Document extends Model implements
    Attachable,
    OptimizesImages,
    ResizesImages,
    CascadesFileDeletion
{
    use UpdatesOnTouch;

    protected $guarded = [];

    protected $touches = ['documentable'];

    public function file(): Relation
    {
        return $this->belongsTo(File::class);
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function imageWidth(): ?int
    {
        return Config::get('liberu.documents.imageWidth');
    }

    public function imageHeight(): ?int
    {
        return Config::get('liberu.documents.imageHeight');
    }

    public static function cascadeFileDeletion(File $file): void
    {
        self::whereFileId($file->id)->first()->delete();
    }

    public function store(array $request, array $files)
    {
        $class = Relation::getMorphedModel($request['documentable_type'])
            ?? $request['documentable_type'];

        $documentable = $class::query()->find($request['documentable_id']);

        return Collection::wrap($files)
            ->map(fn ($file) => $this->attemptStore($documentable, $file))
            ->filter()
            ->values();
    }

    public function scopeFor(Builder $query, array $params): Builder
    {
        return $query->whereDocumentableId($params['documentable_id'])
            ->whereDocumentableType($params['documentable_type']);
    }

    public function scopeFilter(Builder $query, ?string $search): Builder
    {
        return $query->when($search, fn ($query) => $query
            ->where(fn ($query) => $query
                ->whereHas('file', fn ($file) => $file
                    ->where('original_name', 'LIKE', '%'.$search.'%'))
                ->orWhere('text', 'LIKE', '%'.$search.'%')));
    }

    private function ocr($document)
    {
        if ($this->ocrable($document)) {
            Job::dispatch($document);
        }

        return $this;
    }

    private function ocrable($document)
    {
        return $document->documentable instanceof Ocrable
            && $document->file->mime_type === 'application/pdf';
    }

    private function attemptStore($documentable, UploadedFile $file): ?self
    {
        try {
            return DB::transaction(fn () => $this
                ->storeFile($documentable, $file));
        } catch (\Throwable) {
            return null;
        }
    }

    private function storeFile($documentable, UploadedFile $file): self
    {
        $document = $documentable->documents()->create();
        $file = File::upload($document, $file);
        $document->file()->associate($file)->save();

        $this->ocr($document);

        return $document;
    }

    public function delete()
    {
        parent::delete();
        $this->file->delete();
    }
}
