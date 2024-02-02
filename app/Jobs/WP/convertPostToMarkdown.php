<?php

namespace App\Jobs\WP;

use App\Models\Post;
use App\WP\WPMarkdownFix;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\HTMLToMarkdown\HtmlConverter;

class convertPostToMarkdown implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    private $post;

    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $converter = new HtmlConverter();
            $markdown = $converter->convert($this->post->body['content']);
            $sanitaseMarkdown = new WPMarkdownFix($markdown);
            $output = $sanitaseMarkdown->replaceFiguresInMarkdown();
            $this->post->update(['output' => $output]);
        } catch (\Throwable $th) {
            logger()->error(
                'Error converting post to markdown' . $th->getMessage()
            );
        }
    }
}
