<?php

namespace App\Constants;

class FileInfo
{

    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    |
    | This trait basically contain the path of files and size of images.
    | All information are stored as an array. Developer will be able to access
    | this info as method and property using FileManager class.
    |
    */

    public function fileInfo()
    {

        $data['profilephoto'] = [
            'path'      => 'assets/images/profilephoto',
            'size'      => '100x100',
        ];

        $data['userphoto'] = [
            'path' => 'assets/images/userphoto',
            'size' => '100x100',
        ];
        
        $data['signature'] = [
            'path'      => 'assets/images/signature',
            'size'      => '100x100',
        ];

        $data['resume'] = [
            'path'      => 'assets/images/resume',
        ];
        $data['certificate'] = [
            'path'      => 'assets/images/certificate',
        ];
        $data['document'] = [
            'path'      => 'assets/images/document',
        ];

        $data['attachment'] = [
            'path'      => 'assets/images/attachment',
        ];

        $data['logo'] = [
            'path' => 'assets/images/setting',
            'size' => '178x50',
        ];
        
        $data['fav_icon'] = [
            'path' => 'assets/images/setting',
            'size' => '50x50',
        ];
        

        return $data;
    }
}