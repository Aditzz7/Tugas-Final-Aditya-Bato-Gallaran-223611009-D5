<?php
namespace Src\Controllers;

use Src\Config\Database;
use Src\Helpers\Response;
use Src\Helpers\Jwt;
use Src\Validation\Validator;
use PDO;

class AuthController extends BaseController{
    public function login(){
        $in=json_decode(file_get_contents('php://input'),true)??[];
        $v=Validator::make($in,[
            'email'=>'required|email|max:150',
            'password'=>'required|min:6|max:72'
        ]);
        if($v->fails()){
            return $this->error(422,'Validation error',$v->errors());
        }
        $db=Database::conn($this->cfg);
        $s=$db->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?;');
        $s->execute([$in['email']]);
        $u=$s->fetch(PDO::FETCH_ASSOC);
        if(!$u || !password_verify($in['password'], $u['password_hash'])){
            return $this->error(401,'Invalid credentials');
        }
        $payload=[
            'sub'=>$u['id'],
            'name'=>$u['name'],
            'role'=>$u['role'],
            'iat'=>time(),
            'exp'=>time()+600
        ];
        $token=Jwt::sign($payload,$this->cfg['app']['jwt_secret']);
        $this->ok(['token'=>$token]);
    }
}