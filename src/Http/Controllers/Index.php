<?php

namespace LaravelLiberu\Documents\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelLiberu\Documents\Http\Requests\ValidateDocument;
use LaravelLiberu\Documents\Http\Resources\Document as Resource;
use LaravelLiberu\Documents\Models\Document;

class Index extends Controller
{
    public function __invoke(ValidateDocument $request)
    {
        return Resource::collection(
            Document::latest()
                ->with('file.createdBy.avatar')
                ->for($request->validated())
                ->filter($request->get('query'))
                ->get()
        );
    }
}
