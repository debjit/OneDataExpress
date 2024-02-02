<?php

namespace App\Jobs\Hashnode;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishAPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $post;
    private $hashnode;
    private $settings;

    public function __construct($post, $hashnode, $settings = [])
    {
        $this->post = $post;
        $this->hashnode = $hashnode;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $hashnodeInstence = new \App\Integrations\Hashnode($this->hashnode);
        $hashnodeInstence->publishAPost($this->post, $this->settings);
    }
}
