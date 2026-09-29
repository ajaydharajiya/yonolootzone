<?php
require_once __DIR__ . '/../../cms/config.php';
require_login();
function admin_data($name,$default=[]){ return read_json(CMS_DATA.'/'.$name,$default); }
function admin_save($name,$data){ write_json(CMS_DATA.'/'.$name,$data); }
function redirect_admin($url='dashboard.php'){ header('Location: '.$url); exit; }
function flash($type,$msg){ $_SESSION['admin_flash']=[$type,$msg]; }
function get_flash(){ $x=$_SESSION['admin_flash']??null; unset($_SESSION['admin_flash']); return $x; }
function post_str($key,$default=''){ return trim((string)($_POST[$key]??$default)); }
function bool_post($key){ return !empty($_POST[$key]); }
function safe_url($u){ return trim((string)$u); }
