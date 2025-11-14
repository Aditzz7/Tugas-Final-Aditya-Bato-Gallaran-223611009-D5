<?php
namespace Src\Controllers;

class VersionController extends BaseController{
    public function show():void{
        $version=$this->cfg['app']['version']??'1.0.0';
        $this->ok(['version'=>$version]);
    }
}