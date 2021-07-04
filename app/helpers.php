<?php

use voku\helper\HtmlDomParser;

function curlSetup($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    $html = curl_exec($curl);
    curl_close($curl);

    return HtmlDomParser::str_get_html($html);
}

function zipiDirect($url)
{
    $url = $url;
    $baseUrl = explode("/", str_replace("https://", "", $url))[0];
    $html = curlSetup($url);
    preg_match("/document\.getElementById\('dlbutton'\)\.href =(.*?);/si", $html, $link);
    preg_match("/\/d\/(\w+)\//si", $link[1], $param);
    preg_match("/ \+ \((.*?) \+ (.*?)\) \+ /si", $link[1], $param2);
    preg_match("/\+ \"(.*?)\";/si", $html, $param3);
    // echo (83516 % 51245 + 83516 % 913);
    $satu = intval(explode(" % ", $param2[1])[0]) % intval(explode(" % ", $param2[1])[1]);
    $dua = intval(explode(" % ", $param2[2])[0]) % intval(explode(" % ", $param2[2])[1]);
    $tiga = $satu + $dua;
    $res =  "https://" . $baseUrl . "/d/" . $param[1] . "/" . $tiga . $param3[1];
    $data = [];
    $data['url'] = $res;
    $data['title'] = explode(' - ', $html->findOne('title')->text())[1];
    return $data;
}

function mediaDirect($url)
{
    $html = curlSetup($url);
    $data = [];
    $url = $html->findOne('#downloadButton')->getAttribute('href');
    $title = $html->findOne('body > div.mf-dlr.page.ads-alternate > div.content > div.center > div > div.dl-info > div > div.filename')->text();

    $data['url'] = $url;
    $data['title'] = $title;
    return $data;
}

function driveDownload($url)
{
    $html = curlSetup($url);
    $data = [];
    $newUrl = null;

    if (strpos($url, '/uc')) {
        $data['title'] = $html->findOne('#uc-text > p.uc-warning-subcaption > span > a')->text();
        preg_match("/uc\?id=(.*?)&/si", $url, $link);
        if ($link) {
            $newUrl = $link[1];
            // echo $link[1];
        } else {
            preg_match("/export=download&id=(.*?)$/si", $url, $link2);
            $newUrl =  $link2[1];
        }
    } else {
        $newUrl = (explode('/', parse_url($url)['path'])[3]);
        $data['title'] = str_replace(' - Google Drive', "", $html->findOne('title')->text());
    }
    if (isset($newUrl)) {
        $id = $newUrl;
        $__url = filter_var(strip_tags($id), FILTER_SANITIZE_STRING);
        $_url = str_replace("https://drive.google.com/file/d/", "", $__url);
        $iurl = str_replace("/view?usp=sharing", "", $_url);
        $iiurl = str_replace("/view?usp=drivesdk", "", $iurl);
        $iiiurl = str_replace("/view", "", $iiurl);
        $iiiiurl = str_replace("/preview", "", $iiiurl);
        $idd = $iiiiurl;
        $start = curl_init("https://drive.google.com/uc?id=" . $idd . "&authuser=0&export=download");
        curl_setopt_array($start, array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_HTTPHEADER => [
                'accept-encoding: gzip, deflate, br',
                'content-length: 0',
                'content-type: application/x-www-form-urlencoded;charset=UTF-8',
                'origin: https://drive.google.com',
                'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.4430.93 Safari/537.36',
                'x-client-data: CIS2yQEIorbJAQjBtskBCKmdygEIlqzKAQj4x8oBCNHhygEI5JzLAQipncsBCOidywEIoKDLAQjf78sB', // Replace with your client-data
                'x-drive-first-party: DriveWebUi',
                'x-json-requested: true'
            ],
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
            CURLOPT_POSTFIELDS => [],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        ));
        $res = curl_exec($start);
        $res_http = curl_getinfo($start, CURLINFO_HTTP_CODE);
        curl_close($start);
        if ($res_http == '200') {
            $json_data = json_decode(str_replace(')]}\'', '', $res));
            if (isset($json_data->downloadUrl)) {
                $data['url'] = $json_data->downloadUrl;
            }
        } else {
            return $res_http;
        }
    }
    return $data;
}
