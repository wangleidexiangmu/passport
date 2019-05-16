<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  Illuminate\Support\Facades\DB;
class regController extends Controller
{
    public function open()
    {
        $res= file_get_contents("php://input");
        $data=base64_decode($res);
        storage_path('key/ras_private.pem');
        $k=openssl_get_publickey('file://'.storage_path('key/rsa_public_key.pem'));
        // var_dump($k);
        $sl=openssl_public_decrypt($data,$finaltext,$k,OPENSSL_PKCS1_PADDING);
        // echo "String crypted: $finaltext";
        // echo 123;
        //var_dump(json_decode($finaltext,true));exit;
        $uid=DB::table('userreg')->insert(json_decode($finaltext,true));
        if($uid){
            $response=[
                'errno'=>0,
                'msg'=>'ok'
            ];
        }else{
            $response=[
                'errno'=>50003,
                'msg'=>'注册失败'
            ];
        }
        die(json_encode($response));

    }
}
