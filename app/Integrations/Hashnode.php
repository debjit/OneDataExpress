<?php

namespace App\Integrations;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class Hashnode
{
    // Check production before post
    protected $hashnodeApiEndpoint;
    protected $publicationId;
    protected $hashnodeApiKey;


    public function __construct($hashnodeBlogId)
    {

        $hashnodeBlogDetails = Setting::findOrFail($hashnodeBlogId);
        $this->hashnodeApiEndpoint = $hashnodeBlogDetails->api_endpoint;
        $this->publicationId = $hashnodeBlogDetails->publication_id;
        $this->hashnodeApiKey = $hashnodeBlogDetails->api_key;
    }

    public function checkCurrentUser()
    {
        try {

            $query =
                'query Me {
        me {
          id
          username
          name
        }
      }';

            $response = Http::asJson()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->hashnodeApiKey
                ])
                ->post($this->hashnodeApiEndpoint, [
                    'query' => $query,
                ]);

            $data = $response->json();

            if (!empty($data['errors'])) {
                return false;
            } else {
                return true;
            }
        } catch (\Throwable $th) {
            logger()->error($th);
            return false;
        }
    }

    public function publishAPost($post, $settings = [])
    {
        $currentPost = Post::findOrFail($post);
        $currentPost->load('site');

        if (empty($currentPost->output)) {
            logger()->error($post->id . " . Post output is empty");
            return false;
        }

        $query =
            'mutation PublishPost($input: PublishPostInput!) {
        publishPost(input: $input) {
            post {
            id
            title
            slug
            url
            }
         }
        }';

        $currentPostTags = $currentPost->meta['tags'];
        $allTagsFromSite = $currentPost->site['details']['tags'];

        // Tags needed
        $tags = [];

        // Todo: Refactor this
        foreach (array_slice($currentPostTags, 0, 5) as $tag) {
            $matchedTag = array_filter($allTagsFromSite, function ($t) use ($tag) {
                return $t['id'] == $tag;
            });

            $matchedTag = array_values($matchedTag);

            if (!empty($matchedTag)) {
                $tags[] = [
                    // $matchedTag[0]
                    "name" => $matchedTag[0]['name'],
                    "slug" => $matchedTag[0]['slug']
                ];
            };
        }

        $input = [
            "title" => $currentPost->title,
            "contentMarkdown" => $currentPost->output,
            "slug" => $currentPost->meta['slug'],
            "disableComments" => true,
            "publicationId" => $this->publicationId,
            "tags" => $tags
            // ... other input fields
            // "subtitle" => "xyz789",
            // "publicationId" => "your-publication-id", //input
            // "tags" => [
            //     [
            //         "name" => "php",
            //         "slug" => "php"
            //     ],
            //     [
            //         "name" => "graphql",
            //         "slug" => "graphql"
            //     ],
            // ]

        ];

        if (!empty($settings['originalArticleURL'])) {
            $input['originalArticleURL'] = $currentPost->meta['link'];
        }

        $variables = [
            'input' => $input
        ];

        $response = Http::asJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->hashnodeApiKey
            ])
            ->post($this->hashnodeApiEndpoint, [
                'query' => $query,
                'variables' => $variables,
            ]);

        $data = $response->json();

        if ($response->successful()) {

            if (!empty($data['errors'])) {
                print_r($data['errors'][0]['message']);
                return;
            }

            // $post = $data['data']['publishPost']['post'];
            // $result = $data['data'];
            $currentPost->meta = array_merge($currentPost->meta, $data['data']['publishPost']);
            $currentPost->status = 3;
            $currentPost->save();
            return true;
        } else {
            logger()->error("Error creating post: " . $data['errors']);
            return false;
        }
    }
}
