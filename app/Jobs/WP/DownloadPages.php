<?php

namespace App\Jobs\WP;

use App\Models\Post;
use App\Models\Site;
use App\Wp\WPApiV2;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

// Todo
// 1. Get the page data,
// 2. Add the category and tasgs
// 3. Convert it to markdown and output

class DownloadPages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $site;
    public $url;
    public $settings;

    public function __construct(Site $site, $url, $settings = ['convert' => true])
    {
        $this->site = $site;
        $this->url = $url;
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // $url = $this->site->url . "/wp-json/wp/v2/posts?per_page=" . $this->page;
        $fetchDataResponse = self::fetchFromUrl($this->url);
        $data = $fetchDataResponse->json();

        if ($fetchDataResponse->ok()) {

            foreach ($data as $value) {
                $item = [
                    'site_id' => $this->site->id,
                    'status' => 0,
                    'title' => $value['title']['rendered'],
                    'post_id' => $value['id'],
                    'body' => $value['content']['rendered'],
                    'meta' => [
                        'slug' => $value['slug'],
                        'link' => $value['link'],
                        'excerpt' => $value['excerpt']['rendered'],
                        'author' => $value['author'],
                        'featured_media' => $value['featured_media'],
                        'comment_status' => $value['comment_status'],
                        'sticky' => $value['sticky'],
                        'categories' => $value['categories'],
                        'tags' => $value['tags'],
                    ]
                ];

                $createdPost =  Post::updateOrCreate(["site_id" => $item['site_id'], 'post_id' => $item['post_id']], $item);

                if ($this->settings['convert']) {
                dispatch(new convertPostToMarkdown($createdPost));
                }
            }

            // $this->site->posts()->createMany($items);

        }
    }

    private static function fetchFromUrl($url)
    {
        $response = Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
            // "User-Agent" => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0'
        ])
            ->withOptions(['verify' => false])
            ->get($url);

        return $response;
    }
}
