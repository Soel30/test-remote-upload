<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('upload');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = explode('https://', str_replace("\r\n", "", $request->link));
        foreach ($data as $link) {
            if ($link) {
                $new = explode(".", $link)[1];
                $newlink = "https://" . $link;
                switch ($new) {
                    case "zippyshare":
                        $media = zipiDirect($newlink);
                        $dl = file_get_contents($media['url']);
                        Storage::disk('local')->put($media['title'], $dl);
                        echo 'upload zippyshare selesai';
                        break;
                    case "mediafire":
                        $media = mediaDirect($newlink);
                        $path  = "../storage/app/" . $media['title'];
                        $fp = fopen($path, "w+");
                        $ch = curl_init($media['url']);
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_exec($ch);
                        $st_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        fclose($fp);
                        if ($st_code == 200)
                            echo 'upload mediafire selesai';

                        else
                            echo 'Error downloading file!';
                        break;
                    case "google":
                        $media = driveDownload($newlink);
                        $path  = "../storage/app/" . $media['title'];
                        $fp = fopen($path, "w+");
                        $ch = curl_init($media['url']);
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_exec($ch);
                        $st_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        curl_close($ch);
                        fclose($fp);
                        if ($st_code == 200)
                            echo 'upload drive selesai';

                        else
                            echo 'Error downloading file!';
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
