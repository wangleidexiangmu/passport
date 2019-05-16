<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class LoginController extends Controller
{
    public function login(Request $request){
        // var_dump(getcookie());
        $res= file_get_contents("php://input");
        $data=base64_decode($res);
      // echo  storage_path('key/ras_private.pem');
        $k=openssl_get_publickey('file://'.storage_path('key/rsa_public_key.pem'));
        // var_dump($k);
        $sl=openssl_public_decrypt($data,$finaltext,$k,OPENSSL_PKCS1_PADDING);
        //echo $finaltext;exit;
        // var_dump(json_decode($finaltext,true));exit;
        $json=json_decode($finaltext,true);
        //var_dump($json);
        $email=$json['email'];
        $pass=$json['pass'];
         //echo $email;exit;

       //echo $pass;
        // exit;
        // $pass=$request->input('pass');
        $u=DB::table('userreg')->where(['email'=>$email])->first();
        if($u){
            if(password_verify($pass,$u->pass)){
                $token=$this->getlogintoken($u->uid);
                $redis_token_key='login_token:uid:'.$u->uid;
                Redis::set($redis_token_key,$token);
                Redis::expire($redis_token_key,64800);
                $response=[
                    'errno'=>0,
                    'msg'=>'ok',
                    'uid'=>$u->uid,
                    'data'=>[
                        'token'=>$token
                    ]
                ];
                die(json_encode($response));
            }else{
                $response=[
                    'errno'=>50007,
                    'msg'=>'登录失败'
                ];
                die(json_encode($response,JSON_UNESCAPED_UNICODE));
            }
        }else{
            $response=[
                'errno'=>50004,
                'msg'=>'用户不存在'
            ];
            die(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
    }
    protected function getlogintoken($uid){
        $token=substr(md5($uid.time().Str::random(10)),5,15);
        return $token;
    }
}
