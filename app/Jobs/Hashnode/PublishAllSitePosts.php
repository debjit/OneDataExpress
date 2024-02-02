<?php

namespace App\Jobs\Hashnode;

use App\Models\Setting;
use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishAllSitePosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $site;
    private $hashnode;



    /**
     * Create a new job instance.
     */
    public function __construct($siteId, $hashnodeId)
    {
        $this->site = Site::with(['posts:id'])->findOrFail($siteId);
        $this->hashnode = Setting::findOrFail($hashnodeId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $postIds = $this->site->posts->pluck('id');

        foreach ($postIds as $postId) {
            dispatch(new PublishAPost($postId, $this->hashnode->id));
        }
    }
}
