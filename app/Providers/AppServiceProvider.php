<?php

namespace Castle\Providers;
use Castle\Attachable;
use Castle\Discussion;
use Castle\Document;
use Castle\Resource;

use Illuminate\Support\ServiceProvider;
use Storage;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $deleteAttachments = function($item) {
            assert($item instanceOf Attachable, 'object does not use Attachable trait');

            foreach ($item->attachments as $attachment) {
                if (Storage::disk('attachments')->has($attachment)) {
                    Storage::disk('attachments')->delete($attachment);
                }
            }
        };

        Discussion::deleted($deleteAttachments);
        Document::deleted($deleteAttachments);
        Resource::deleted($deleteAttachments);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        View::composer('*', function() {
            include_once app_path('Http/helpers.php');
        });
    }
}
