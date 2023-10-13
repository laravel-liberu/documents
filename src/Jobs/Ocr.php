<?php

namespace LaravelLiberu\Documents\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use LaravelLiberu\Documents\Models\Document;
use LaravelLiberu\Ocr\Ocr as Service;

class Ocr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Document $document)
    {
        $this->queue = config('liberu.documents.queues.ocr');
    }

    public function handle()
    {
        $path = Storage::path($this->document->file->path);
        $text = (new Service($path))->text();

        $this->document->update([
            'text' => preg_replace('/\s+/', ' ', $text),
        ]);
    }
}
