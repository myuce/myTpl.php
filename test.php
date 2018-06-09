<?php
require 'myTpl.php';
use myuce\myTpl;
$tpl = new myTpl('tpl','cache');
$tpl->set('name','john');
$tpl->set('surname','doe');
$tpl->set('family',[
['name'=>'jane','surname'=>'doe','relation'=>'wife'],
['name'=>'baby','surname'=>'doe','relation'=>'child'],
]);
$user = new stdClass;
$user->logged = true;
$tpl->set('user',$user);
$tpl->load('test');