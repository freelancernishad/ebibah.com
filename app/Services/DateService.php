<?php
namespace App\Services;

use Carbon\Carbon;

class DateService
{


    public static function formatArticleDates($articles)
    {
        foreach ($articles as $article) {
           $date =  Carbon::parse($article->date)->format('d M, Y H:i');
            $article->date = int_en_to_bn(month_name_en_to_bn_text($date));
            // $article->date = Carbon::parse($article->date)->format('Y-d-M H:i:s');
            // Add any other formatting or modifications if needed
        }
        return $articles;
    }


    public static function formatArticleDate($article)
    {

           $date =  Carbon::parse($article->date)->format('d M, Y H:i');
            $article->date = int_en_to_bn(month_name_en_to_bn_text($date));
            // $article->date = Carbon::parse($article->date)->format('Y-d-M H:i:s');
            // Add any other formatting or modifications if needed

        return $article;
    }


}
