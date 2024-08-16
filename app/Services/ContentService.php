<?php
namespace App\Services;

class ContentService
{
    public static function sortArticleContent($article)
    {
        $content_without_tags = preg_replace('/<[^>]*>/', '', $article->content);
        $words = preg_split('/\s+/', $content_without_tags);
        $max_words = 15;
        $shortened_words = array_slice($words, 0, $max_words);
        $shortened_content = implode(' ', $shortened_words);
        if (count($words) > $max_words) {
            $shortened_content .= '...';
        }
        $article->content = $shortened_content;
        return $article;
    }

    public static function sortArticleContents($articles)
    {
        foreach ($articles as $article) {
            $article = self::sortArticleContent($article);
        }
        return $articles;
    }
}
