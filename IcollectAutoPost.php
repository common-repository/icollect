<?php
// PHP 面向对象 https://www.runoob.com/php/php-oop.html
// 命名规范 https://www.cnblogs.com/lishanlei/p/9483309.html

require_once 'Utils.php';

class IcollectAutoPost {
  /* 成员变量 */
  var $mVerion;
  var $mParams=[];
  var $mSettingFile;

  function __construct( $verion, $settingFile='./setting.php' ) {
    // $this->set_error_handler();
    $this->mVerion = $verion;
    $this->mSettingFile = $settingFile;
    $this->request();
  }

  /* 成员函数 */

  function set_error_handler(){
    function exception_error_handler($errno, $errstr, $errfile, $errline ) {
      throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
    set_error_handler("exception_error_handler");
  }

  function setting(){
    if(file_exists($this->mSettingFile)){
      header('Location: ' . $this->mSettingFile);
      exit();
    }
  }

  function getCategory(){
    throw new Exception('getCategory 未在子类中实现');
  }

  function getConfigPassword(){
    throw new Exception('getConfigPassword 未在子类中实现');
  }
  
  function post(){
    throw new Exception('post 未在子类中实现');
  }
  
  function findPostByTitle(){
    throw new Exception('findPostByTitle 未在子类中实现');
  }
  
  function checkCategory($category){
    throw new Exception('checkCategory 未在子类中实现');
  }
  
  function checkUser($userId){
    throw new Exception('checkUser 未在子类中实现');
  }
  
  function getFilePath(){
    throw new Exception('getFilePath 未在子类中实现');
  }
  
  function getVertion(){
    return $this->mVerion;
  }

  // 处理请求方法
  function request(){
    $this->handleRequest();
  }

  // 格式化返回方法
  function success($data = "", $message = ""){
    header('Content-Type:application/json; charset=utf-8');
    $body=array(
      "result" => 1, 
      "code" => 0, 
      "data" => $data, 
      "message" => $message,
    );
    die(json_encode($body));
  }

  function error($code = 0, $data = "", $message = ""){
    header('Content-Type:application/json; charset=utf-8');
    $body=array(
      "result" => 0, 
      "code" => $code, 
      "data" => $data, 
      "message" => $message,
    );
    die(json_encode($body));
  }
  
  function handleRequest(){
    $publicType=isset($_GET['publicType'])?sanitize_text_field(IcollectUtils::htmlDecode($_GET['publicType'])):'setting';
    $this->$publicType();
  }
    
  function checkPassword(){
    $config_password=$this->getConfigPassword();
    $password=isset($_POST['password'])?sanitize_text_field($_POST['password']):'';
    if (empty($password) || $password != $config_password) {
      $this->error('PASSWORD_ERROR', "password error", "提交的发布密码错误");
    }
  }
  
  function version(){
    $this->checkPassword();
    $data=$this->getVertion();
    $this->success($data);
  }
  
  function category(){
    $this->checkPassword();
    $data=$this->getCategory();
    $this->success($data);
  }
  
  function checkUnique($isUnique=false){
    if ($isUnique) {
      $url=$this->findPostByTitle();
      if (!empty($url)) {
        $this->success(array(
            "url" => $url
        ));
      }
    }
  }
  
  function setParam($param, $value){
    $this->mParams[$param]=$value;
  }
  
  function getParam($param){
    return isset($this->mParams[$param]) ? $this->mParams[$param] : '';
  }
  
  function getParams(){
    return $this->mParams;
  }
  
  function validField($field,$msg){
    $data = isset($_POST[$field]) ? sanitize_text_field($_POST[$field]) : '';
    if(empty($data)) {
      $this->error('ERROR_FIELD', "not null", $msg);
    }
  }

  function getExt($str) {
    $index=strrpos($str,'.');
    if($index>0){
      return strtolower(substr($str,$index+1));
    }else{
      return null;
    }
  }

  function downloadImages(){
    try {
      if ($this->getParam('isDownload')) {
        $imgList = $this->getParam('imgList');
        if ($imgList) {
          $imgListArr = explode(',', $imgList);
          if (is_array($imgListArr)) {
            $upload_dir = $this->getFilePath();
            foreach ($imgListArr as $imgUrl) {
              $urlItemArr = explode('/', $imgUrl);
              $itemLen = count($urlItemArr);
              if ($itemLen >= 4) {
                $fileRelaPath = implode('/', array_slice($urlItemArr,3,$itemLen-4));
                $imgName = $urlItemArr[$itemLen - 1];
                $imgName=preg_replace('/\?.*/i', "", $imgName);
                // var_dump($fileRelaPath,$imgName);  
                if(!in_array($this->getExt($imgName), array('jpg','jpeg','bmp','gif'))){
                  $imgName=$imgName.'.jpg';
                }
                $imgName=preg_replace('/[\/:*?"<>|\\\]/i', "", $imgName);
                $filePath = $upload_dir . '/' . $fileRelaPath;
                if (IcollectUtils::create_folders($filePath)) {
                  $file = $filePath . '/' . $imgName;
                  if (!file_exists($file)) {
                    $data = file_get_contents($imgUrl);
                    file_put_contents($file, $data);
                  }
                }
              }
            }
          }
        }
      }
    }catch(\Exception $ex) {
      return 1;
    }
  }
}
?>