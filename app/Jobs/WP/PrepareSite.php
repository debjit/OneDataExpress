<?php

namespace App\Jobs\WP;

use App\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class PrepareSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $site;
    public $baseUrl;
    public $postPerRequest = 10;

    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->baseUrl = $site->url . "/wp-json/wp/v2";
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $array = ['categories', 'tags',];
        $result = [];

        foreach ($array as $item) {

            $url = $this->baseUrl . "/" . $item . "?per_page=" . $this->postPerRequest;

            $fetchDataResponse = self::fetchFromUrl($url);


            $data = $fetchDataResponse->json();


            if ($fetchDataResponse->ok()) {
                // $totalRecords = $fetchDataResponse->header('X-WP-Total');
                $totalPages = $fetchDataResponse->header('X-WP-TotalPages');


                // Get the data if have more then 1 page.
                if ($totalPages > 1) {
                    for ($i = 2; $i <= $totalPages; $i++) {
                        $url = $this->baseUrl . "/" . $item . "?per_page=" . $this->postPerRequest . "&page=" . $i;
                        $fetchNextDataResponse = self::fetchFromUrl($url);
                        $data = array_merge($data, $fetchNextDataResponse->json());
                    }
                }

                // Get the data what is needed.
                foreach ($data as $value) {
                    $d = [
                        'id' => $value['id'],
                        'description' => $value['description'],
                        'name' => $value['name'],
                        'slug' => $value['slug'],
                    ];
                    $result[$item][] = $d;
                }
            } else {
                return false;
            }
        }
        $this->site->update([
            'details' => $result,
            'status' => 1,
        ]);
    }

    // Only fetches all the information from url does not directly call this
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
