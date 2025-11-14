<?php
namespace Src\Controllers;

use Src\Repositories\UserRepository;
use Src\Validation\Validator;

class UserController extends BaseController{
    public function index(){
        $p=(int)($_GET['page']??1);
        $per=(int)($_GET['per_page']??10);
        $search=$_GET['search']??'';
        $sort=$_GET['sort']??'id';
        $direction=$_GET['direction']??'DESC';
        $role=$_GET['role']??'';
        $validRoles=['admin','user'];
        foreach($validRoles as $validRole){
            if(isset($_GET[$validRole])){
                $role=$validRole;
                break;
            }
        }
        $repo=new UserRepository($this->cfg);
        $this->ok($repo->paginate(max(1,$p),min(100,max(1,$per)),$search,$sort,$direction,$role));
    }
    public function show($id){
        $repo=new UserRepository($this->cfg);
        $u=$repo->find((int)$id);
        $u?$this->ok($u):$this->error(404,'User not found');
    }

    public function store(){
        $contentType=$_SERVER['CONTENT_TYPE']??'';
        $isMultipart=str_contains($contentType,'multipart/form-data');
        $isJson=str_contains($contentType,'application/json');
        if($isMultipart){
            $in=$_POST;
            $avatarPath=null;
            if(!empty($_FILES['avatar'])){
                $avatarPath=$this->uploadAvatar($_FILES['avatar']);
                if(!$avatarPath){
                    return $this->error(422,'Avatar upload failed');
                }
            }
        }elseif($isJson){
            $in=json_decode(file_get_contents('php://input'),true)??[];
            $avatarPath=null;
        }else{
            $in=$_POST;
            $avatarPath=null;
        }
        $v=Validator::make($in,[
            'username'=>'required|min:3|max:100',
            'email'=>'required|email|max:150',
            'password'=>'required|min:6|max:72',
            'role'=>'enum:user,admin'
        ]);
        if($v->fails()){
            return $this->error(422,'Validation error',$v->errors());
        }
        $hash=password_hash($in['password'],PASSWORD_DEFAULT);
        $repo=new UserRepository($this->cfg);
        try{
            $this->ok($repo->create($in['username'],$in['email'],$hash,$in['role']??'user',$avatarPath),201);
        } catch (\Throwable $e) {
            $this->error(400, 'Create failed', ['details' => $e->getMessage()]);
        }
    }

    public function update($id){
        $contentType=$_SERVER['CONTENT_TYPE']??'';
        $isMultipart=str_contains($contentType,'multipart/form-data');
        $isJson=str_contains($contentType,'application/json');
        if($isMultipart){
            $in=$_POST;
            $avatarPath=null;
            if(!empty($_FILES['avatar'])){
                $avatarPath=$this->uploadAvatar($_FILES['avatar']);
                if(!$avatarPath){
                    return $this->error(422,'Avatar upload failed');
                }
            }
        }elseif($isJson){
            $in=json_decode(file_get_contents('php://input'),true)??[];
            $avatarPath=null;
        }else{
            $in=$_POST;
            $avatarPath=null;
        }
        $validationRules=[
            'username'=>'required|min:3|max:100',
            'email'=>'required|email|max:150',
            'role'=>'enum:user,admin'
        ];
        if(!empty($in['password'])){
            $validationRules['password']='min:6|max:72';
        }
        $v=Validator::make($in,$validationRules);
        if($v->fails()){
            return $this->error(422,'Validation error',$v->errors());
        }
        $repo=new UserRepository($this->cfg);
        $passwordHash=null;
        if(!empty($in['password'])){
            $passwordHash=password_hash($in['password'],PASSWORD_DEFAULT);
        }
        $this->ok($repo->update((int)$id,$in['username'],$in['email'],$in['role'],$avatarPath,$passwordHash));
    }
    public function destroy($id){
        $repo=new UserRepository($this->cfg);
        $ok=$repo->delete((int)$id);
        $ok?$this->ok(['deleted'=>true]):$this->error(400,'Delete failed');
    }
    private function uploadAvatar($file){
        if($file['error']!==UPLOAD_ERR_OK){
            return false;
        }
        if($file['size']>1*1024*1024){
            return false;
        }
        $finfo=new \finfo(FILEINFO_MIME_TYPE);
        $mime=$finfo->file($file['tmp_name']);
        $allowed=['image/png'=>'png','image/jpeg'=>'jpg'];
        if(!isset($allowed[$mime])){
            return false;
        }
        $imageInfo=getimagesize($file['tmp_name']);
        if($imageInfo===false){
            return false;
        }
        $name=bin2hex(random_bytes(8)).'.'.$allowed[$mime];
        $uploadDir=__DIR__.'/../../public/uploads/avatars/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir,0755,true);
        }
        $dest=$uploadDir.$name;
        if(move_uploaded_file($file['tmp_name'],$dest)){
            return "/uploads/avatars/$name";
        }
        return false;
    }
}