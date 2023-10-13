<?php

namespace LaravelLiberu\Documents;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use LaravelLiberu\Documents\Models\Document;
use LaravelLiberu\Documents\Policies\Document as Policy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Document::class => Policy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();
    }
}
