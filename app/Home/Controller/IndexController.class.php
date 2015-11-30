<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
    	$this->title = C("TITLE");
    	$this->display();
    }
}