<?php
namespace Src\Validation;

class Validator{
    private array $errors=[];
    private array $data;
    private array $rules;
    public static function make(array $data,array $rules):self{
        return new self($data,$rules);
    }
    public function __construct(array $data,array $rules){
        $this->data=$data;
        $this->rules=$rules;
        $this->validate();
    }
    public function fails():bool{
        return !empty($this->errors);
    }
    public function errors():array{
        return $this->errors;
    }
    private function validate(){
        foreach($this->rules as $field=>$rules){
            $rules=explode('|',$rules);
            $value=$this->data[$field]??null;
            foreach($rules as $rule){
                if($rule=='required'&&empty($value)){
                    $this->errors[$field][]="The $field field is required.";
                }elseif($rule=='email'&&$value!==null&&!filter_var($value,FILTER_VALIDATE_EMAIL)){
                    $this->errors[$field][]="The $field must be a valid email address.";
                }elseif($rule=='numeric'&&$value!==null&&!is_numeric($value)){
                    $this->errors[$field][]="The $field must be a number.";
                }elseif(strpos($rule,'min:')===0){
                    $min=(int)substr($rule,4);
                    if($value!==null&&strlen($value)<$min){
                        $this->errors[$field][]="The $field must be at least $min characters.";
                    }
                }elseif(strpos($rule,'max:')===0){
                    $max=(int)substr($rule,4);
                    if($value!==null&&strlen($value)>$max){
                        $this->errors[$field][]="The $field may not be greater than $max characters.";
                    }
                }elseif(strpos($rule,'enum:')===0){
                    $allowed=explode(',',substr($rule,5));
                    if($value!==null&&!in_array($value,$allowed)){
                        $this->errors[$field][]="The $field must be one of: ".implode(', ',$allowed);
                    }
                }
            }
        }
    }
}
