<?php
header("content-type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include('simple_html_dom.php');

        $html = new simple_html_dom();
        $html->load_file('https://tamilayurvedic.com/category/%e0%ae%85%e0%ae%b4%e0%ae%95%e0%af%81/%e0%ae%aa%e0%af%86%e0%ae%a3%e0%af%8d%e0%ae%95%e0%ae%b3%e0%af%8d-%e0%ae%ae%e0%ae%b0%e0%af%81%e0%ae%a4%e0%af%8d%e0%ae%a4%e0%af%81%e0%ae%b5%e0%ae%ae%e0%af%8d');

        $arrayname = [];

        foreach ($html->find('div.penci-archive__list_posts article h2 a') as $link) {
            if (isset($link)) {

                $html->load_file($link->href);

                foreach ($html->find('h1.entry-title') as $title) {
                    $name = $title->innerText();
                }

                foreach ($html->find('div.post-image img') as $img) {
                    $img_url = $img->src;
                }


                $category_arr = [];

                foreach ($html->find('span.penci-cat-links a') as $tag) {

                    $category = $tag->innerText();

                    $category_arr[] = [$category];
                }


                $arrayname[] = [
                    'name' => $name,
                    'category' => $category_arr,
                    'featuredImage' => $img_url,
                ];
            }
            break;
        }
        
        echo json_encode($arrayname, JSON_UNESCAPED_UNICODE );
        
        
        ?>
    </body>
</html>
