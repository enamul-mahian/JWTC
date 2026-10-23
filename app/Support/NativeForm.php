<?php
namespace App\Support;
use Illuminate\Support\HtmlString;
class NativeForm
{
    private mixed $model=null;
    private function attrs(array $attributes): string
    {
        $result='';foreach($attributes as $name=>$value){
            if($value===false || $value===null)continue;
            if(!preg_match('/^[a-zA-Z_:][a-zA-Z0-9_:.-]*$/D',(string)$name))continue;
            $result.=' '.e($name).'="'.e($value===true?$name:(string)$value).'"';
        }return $result;
    }
    public function open(array $options=[]): HtmlString
    {
        $method=strtoupper($options['method']??'POST');$route=$options['route']??null;
        $action=$route ? (is_array($route)?route(array_shift($route),$route):route($route)) : ($options['url']??url()->current());
        $attributes=['method'=>$method==='GET'?'GET':'POST','action'=>$action];
        if(!empty($options['files']))$attributes['enctype']='multipart/form-data';
        foreach($options as $key=>$value)if(!in_array($key,['route','url','method','files'],true))$attributes[$key]=$value;
        return new HtmlString('<form'.$this->attrs($attributes).'>'.($method!=='GET'?csrf_field():'').(!in_array($method,['GET','POST'],true)?method_field($method):''));
    }
    public function model($model,array $options=[]): HtmlString {$this->model=$model;return $this->open($options);}
    public function close(): HtmlString {$this->model=null;return new HtmlString('</form>');}
    private function value(string $name,$value){return old($name,$value??data_get($this->model,$name));}
    public function text(string $name,$value=null,array $options=[]): HtmlString {return new HtmlString('<input'.$this->attrs(['type'=>'text','name'=>$name,'id'=>$name,'value'=>$this->value($name,$value)]+$options).'>');}
    public function textArea(string $name,$value=null,array $options=[]): HtmlString {return new HtmlString('<textarea'.$this->attrs(['name'=>$name,'id'=>$name]+$options).'>'.e($this->value($name,$value)).'</textarea>');}
    public function file(string $name,array $options=[]): HtmlString {return new HtmlString('<input'.$this->attrs(['type'=>'file','name'=>$name,'id'=>$name]+$options).'>');}
    public function label(string $name,$text=null,array $options=[]): HtmlString {return new HtmlString('<label'.$this->attrs(['for'=>$name]+$options).'>'.e($text??$name).'</label>');}
    public function button($text,array $options=[]): HtmlString {return new HtmlString('<button'.$this->attrs($options+['type'=>'submit']).'>'.e($text).'</button>');}
    public function select(string $name,$choices=[],$selected=null,array $options=[]): HtmlString
    {
        $selected=$this->value($name,$selected);$html='<select'.$this->attrs(['name'=>$name,'id'=>$name]+$options).'>';
        if(array_key_exists('placeholder',$options))$html.='<option value="">'.e($options['placeholder']).'</option>';
        foreach($choices as $value=>$label)$html.='<option value="'.e($value).'"'.((string)$selected===(string)$value?' selected':'').'>'.e($label).'</option>';
        return new HtmlString($html.'</select>');
    }
}
