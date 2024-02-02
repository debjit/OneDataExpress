<?php

namespace App\WP;

// todo: Remove any figure
class WPMarkdownFix
{
    private $markdown;

    function __construct($markdown)
    {
        $this->markdown = $markdown;
    }

    public function replaceFiguresInMarkdown()
    {
        // $pattern = '/<figure[^>]*>(.*?)<\/figure>/s';
        $pattern = '/<figure[^>]*?>(.*?)<\/figure>/s';
        // $pattern = '/<figure[^>]*>(.*?)</figure>/s';
        // $pattern = '/<figure[^>]*>(.*?)<\/figure>/is';
        $replacement = "$1\n";

        $markdownWithNewlines = preg_replace($pattern, $replacement, $this->markdown);

        return $markdownWithNewlines;
    }
}
