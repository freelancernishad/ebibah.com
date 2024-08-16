<?php


function getUrlFromImgTag($html_content='') {
  $doc = new DOMDocument();
  $doc->loadHTML($html_content);

  $image_tags = $doc->getElementsByTagName('img');

  foreach ($image_tags as $tag) {
     $src = $tag->getAttribute('src');
     if (strpos($src, 'data:image') === 0) {
          $base64Data = explode(',', $src);
          $base64ImageSrcArray = end($base64Data);
          $imageUrl = imagurUpload($base64ImageSrcArray);
          $tag->setAttribute('src', $imageUrl);
      }
  }

  $modified_html = '';
  foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $node) {
      $modified_html .= $doc->saveHTML($node);
  }

  return $modified_html;
}



function imagurUpload($image='',$type='base64'){


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.imgur.com/3/image',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => array('image'=> $image,'type' => $type,'title' => 'Simple upload','description' => 'This is a simple image upload in Imgur'),
  CURLOPT_HTTPHEADER => array(
    'Authorization: Client-ID 0252fcd4c2f2436'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
return  json_decode($response)->data->link;
}






