<?php

namespace App\Wp;

use App\Jobs\WP\DownloadPages;
use App\Jobs\WP\PrepareSite;
use App\Models\Post;
use App\Models\Site;
use App\WP\WPFileNormalise;
use App\WP\WPMarkdownFix;
use Illuminate\Support\Facades\Http;
use League\HTMLToMarkdown\HtmlConverter;
// 1. Get the tags,
// 2. Gets the categories,
// and update the category
// 3. Get the posts, // Queues
// 3.1*. Gets the media original image
// 4. Converts the pages[status 0=  queue, 1 = processed, 2 = error ] //


class WPApiV2
{
    protected $site;
    protected $noauth = true;

    public function __construct($siteId, $noauth = true)
    {
        $site = Site::firstOrFail($siteId);

        $this->site = $site;
        $this->noauth = $noauth;
    }
    // Only fetches all the information from url does not directly call this
    private static function fetchFromUrl($url)
    {
        $response = Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
            // "User-Agent" => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:121.0) Gecko/20100101 Firefox/121.0'
        ])->withOptions(['verify' => false])->get($url);
        return $response;
    }

    // public static function startWPConvert($site)
    // {
    //     //
    //     $postPerRequest = 10;
    //     $baseUrl = $site->url . "/wp-json/wp/v2";
    //     // $baseUrl = "https://theankurtyagi.com/" . "wp-json/wp/v2";
    //     //  'posts'

    //     $array = ['categories', 'tags',];
    //     $result = [];

    //     foreach ($array as $item) {

    //         $url = $baseUrl . "/" . $item . "?per_page=" . $postPerRequest;

    //         $fetchDataResponse = self::fetchFromUrl($url);


    //         $data = $fetchDataResponse->json();


    //         if ($fetchDataResponse->ok()) {
    //             // $totalRecords = $fetchDataResponse->header('X-WP-Total');
    //             $totalPages = $fetchDataResponse->header('X-WP-TotalPages');


    //             // Get the data if have more then 1 page.
    //             if ($totalPages > 1) {
    //                 for ($i = 2; $i <= $totalPages; $i++) {
    //                     $url = $baseUrl . "/" . $item . "?per_page=" . $postPerRequest . "&page=" . $i;
    //                     $fetchNextDataResponse = self::fetchFromUrl($url);
    //                     $data = array_merge($data, $fetchNextDataResponse->json());
    //                 }
    //             }

    //             // Get the data what is needed.
    //             foreach ($data as $value) {
    //                 $d = [
    //                     'id' => $value['id'],
    //                     'description' => $value['description'],
    //                     'name' => $value['name'],
    //                     'slug' => $value['slug'],
    //                 ];
    //                 $result[$item][] = $d;
    //             }
    //         } else {
    //             return false;
    //         }
    //     }

    //     if (empty($result)) {
    //         return false;
    //     }

    //     $site->update([
    //         'details' => $result,
    //         'status' => 1,
    //     ]);

    //     return true;
    // }

    public static function  getPages($site)
    {
        $url = $site->url . "/wp-json/wp/v2/posts?per_page=10";
        $fetchDataResponse = self::fetchFromUrl($url);
        $data = $fetchDataResponse->json();

        if ($fetchDataResponse->ok()) {
            // $totalRecords = $fetchDataResponse->header('X-WP-Total');
            $totalPages = $fetchDataResponse->header('X-WP-TotalPages');

            if ($totalPages > 1) {
                for ($i = 2; $i <= $totalPages; $i++) {
                    $url = $url . "&page=" . $i;
                    $fetchNextDataResponse = self::fetchFromUrl($url);
                    $data = array_merge($data, $fetchNextDataResponse->json());
                }
            }
        } else {
            return false;
        }

        $items = [];

        foreach ($data as $value) {
            $items[] = [
                'site_id' => $site->id,
                'status' => 0,
                'title' => $value['title']['rendered'],
                'body' => [
                    'post_id' => $value['id'],
                    'title' => $value['title']['rendered'],
                    'slug' => $value['slug'],
                    'status' => $value['status'],
                    'link' => $value['link'],
                    'content' => $value['content']['rendered'],
                    'excerpt' => $value['excerpt']['rendered'],
                    'author' => $value['author'],
                    'featured_media' => $value['featured_media'],
                    'comment_status' => $value['comment_status'],
                    'sticky' => $value['sticky'],
                    'categories' => $value['categories'],
                    'tags' => $value['tags'],
                ],
                'meta' => [
                    'raw_response' => $value
                ]
            ];
        }
        // Create many posts
        $site->posts()->createMany($items);
        return true;
    }

    public static function convertHtmlToMarkdown($html)
    {
        try {
            $converter = new HtmlConverter();
            $markdown = $converter->convert($html);
            $sanitaseMarkdown = new WPMarkdownFix($markdown);
            $output = $sanitaseMarkdown->replaceFiguresInMarkdown();
            return $output;
        } catch (\Throwable $th) {
            logger()->error('Error in convertToMarkdown: ' . $th->getMessage());
            return false;
        }
    }

    public static function convertToMarkdown($post)
    {
        try {
            $converter = new HtmlConverter();
            // $extractor = new WPFileNormalise();
            // $modifiedFigure = $extractor->extractMainImage($post->body['content']);
            // $modifiedFigure = $extractor->modifzyImagesInFigure($post->body['content']);
            // dd($modifiedFigure);
            // $cleanTheClasses = $extractor->removeClasses($post->body['content']);
            $markdown = $converter->convert($post->body['content']);
            $sanitaseMarkdown = new WPMarkdownFix($markdown);
            $output = $sanitaseMarkdown->replaceFiguresInMarkdown();
            $post->update([
                'output' => $output,
            ]);
            return true;
        } catch (\Throwable $th) {
            logger()->error('Error in convertToMarkdown: ' . $th->getMessage());
            return false;
        }
    }

    public static function prepareForPostExtraction(Site $site)
    {
        try {
            dispatch(new PrepareSite($site));
            return true;
        } catch (\Exception $e) {
            // Log or handle any exceptions
            logger()->error('Error in prepareForPostExtraction: ' . $e->getMessage());
            // \Log::error('Error in prepareForPostExtraction: ' . $e->getMessage());
            return false;
        }
    }

    public static function getPosts($site, $count = 5, $settings = ['convert' => true])
    {

        try {

            if ($site->status == 0) {
                return;
            }

            // 1. Get the count.
            $baseUrl = $site->url . "/wp-json/wp/v2/posts?per_page=1";

            // 2. Get the data.
            $fetchDataResponse = self::fetchFromUrl($baseUrl);

            // 3. Get the total pages.
            if ($fetchDataResponse->ok()) {
                $totalPages = $fetchDataResponse->header('X-WP-TotalPages');
                $limitCount = (int) ceil($totalPages / $count);

                // 4. Download the total pages.
                for ($i = 1; $i <= $limitCount; $i++) {
                    $url = $site->url . "/wp-json/wp/v2/posts?per_page=" . $count . "&page=" . $i;
                    dispatch(new DownloadPages($site, $url, $settings));
                }
            }

            // $data = $fetchDataResponse->json();
            // dispatch(new DownloadPages($site));
        } catch (\Throwable $th) {
            //throw $th;
            logger()->error('Error in getPosts: ' . $th->getMessage());
        }
    }

    public static function convertSitesPostToMd(Site $site)
    {
        $posts = $site->posts()->where('status', 0)->get();
        foreach ($posts as $post) {
            self::convertToMarkdown($post);
        }
    }

    public static function convertPostToMD(Post $post)
    {
        self::convertToMarkdown($post);
    }
}

