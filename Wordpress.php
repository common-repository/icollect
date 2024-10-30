<?php
/*
Plugin Name: Icollect
Plugin URI: http://www.icollect.net.cn/wordpress
Description: 爱采集(icollect.net.cn)是一个超易用，强大的网页数据采集和发布软件
Version: 1.0.0
Author: Icollect
Author URI: http://www.icollect.net.cn
License: GPLv2 or later
Text Domain: www.icollect.net.cn
 */
require_once 'Utils.php';
require_once 'IcollectAutoPost.php';

class IcollectWordpressAutoPost extends IcollectAutoPost {

  function getConfigPassword(){
    return get_option('icollect_password', "icollect");
  }

  function getCategory(){
    $data = [];
    foreach (get_terms('category', 'orderby=count&hide_empty=0') as $cate){ 
      $data[]=array("value" => urlencode($cate->term_id), "label" => urlencode($cate->name), "parentId" => urlencode($cate->parent));
    }
    return $data;
  }

  function users(){
    global $wpdb;
    $data = [];
    $datas = $wpdb->get_results($wpdb->prepare("SELECT ID,user_login,user_nicename,display_name FROM $wpdb->users order by ID asc "));
    foreach($datas as $key => $value) {
     $data[]=array("uid" => urlencode($value->ID), "username" => urlencode($value->display_name));
    }
    $this->success($data);
  }

  function findPostByTitle(){
    global $wpdb;
    $post_title=isset($_POST['post_title'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_title'])):'';
    $post = $wpdb->get_row($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_title='%s' and post_status!='trash' and post_status!='inherit' ", $post_title));
    if($post){
      return get_home_url() . "/?p={$post->ID}";
    }else{
      return false;
    }
  }
  
  function checkCategory($category){
    if ($category) {
      // 不存在则插入
      $cates = explode(',', $category);
      if (is_array($cates)) {
        $post_cates = array();
        $term = null;
        foreach ($cates as $cate) {
          $cat_id = 0;
          if (is_numeric($cate) && intval($cate) > 0) {
            $cat_name = get_cat_name($cate);
            if (!empty($cat_name)) {
              $cat_id = intval($cate);
            }
          }
          if ($cat_id > 0) {
            array_push($post_cates, $cat_id);
          } else {
            $term = term_exists($cate, "category");
            if ($term === 0 || $term === null) {
              $term = wp_insert_term($cate, "category");
            }
            if ($term !== 0 && $term !== null && !is_wp_error($term)) {
              array_push($post_cates, intval($term["term_id"]));
            }
          }
        }
        if (count($post_cates) > 0) {
          $this->setParam('post_category',$post_cates);
        }
      }
    }
  }
  
  function checkUser($author){
    if ($author) {
      // 不存在则创建
      if ($author == "rand_users") {
        $randNum = IcollectUtils::randFloat();
        $user_id = $wpdb->get_var("SELECT ID FROM $wpdb->users WHERE id >= ((SELECT MAX(id) FROM $wpdb->users)-(SELECT MIN(id) FROM $wpdb->users)) * " . $randNum . "+ (SELECT MIN(id) FROM $wpdb->users) LIMIT 1");
      } else {
        $user_id = username_exists($author);
      }
      $md5author = substr(md5($author), 8, 16);
      if (!$user_id) {
        $user_id = username_exists($md5author);
      }
      if (!$user_id) {
        $random_password = wp_generate_password();
        $userdata = array(
          'user_login' => $md5author,
          'user_pass' => $random_password,
          'display_name' => $author,
        );
        $user_id = wp_insert_user($userdata);
        if (is_wp_error($user_id)) {
          $user_id = 0;
        }
      }
      if ($user_id) {
        $this->setParam('post_author',$user_id);
      }
    }
  }

  function getFilePath() {
    $dir = wp_upload_dir();
    return $dir['basedir'];
  }

  function handlerParams(){

    $this->mParams['post_password']=isset($_POST['post_password'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_password'])):'';
    $this->mParams['post_status']=isset($_POST['post_status'])?IcollectUtils::htmlDecode($_POST['post_status']):'';
    $this->mParams['post_category']=isset($_POST['post_category'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_category'])):'';
    $this->mParams['comment_status']=isset($_POST['comment_status'])?IcollectUtils::htmlDecode($_POST['comment_status']):'';
    $this->mParams['post_author']=isset($_POST['post_author'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_author'])):'';
    $this->mParams['post_title']=isset($_POST['post_title'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_title'])):'';
    $this->mParams['post_content']=isset($_POST['post_content'])?IcollectUtils::htmlDecode($_POST['post_content']):'';
    $this->mParams['post_excerpt']=isset($_POST['post_excerpt'])?IcollectUtils::htmlDecode($_POST['post_excerpt']):'';
    $this->mParams['post_type']=isset($_POST['post_type'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_type'])):'';
    $this->mParams['post_name']=isset($_POST['post_name'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_name'])):'';
    $this->mParams['post_date']=isset($_POST['post_date'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['post_date'])):'';
    $this->mParams['tags_input']=isset($_POST['tags_input'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['tags_input'])):'';
    $this->mParams['feature_url']=isset($_POST['feature_url'])?sanitize_text_field(esc_url(IcollectUtils::htmlDecode($_POST['feature_url']))):'';
    $this->mParams['isDownload']=isset($_POST['isDownload'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['isDownload'])):'';
    $this->mParams['imgList']=isset($_POST['imgList'])?sanitize_text_field(IcollectUtils::htmlDecode($_POST['imgList'])):'';

    if(!in_array($this->mParams['post_status'],array('publish', 'draft'))){
      $this->mParams['post_status']='publish';
    }
    if(!in_array($this->mParams['comment_status'],array('open', 'closed'))){
      $this->mParams['comment_status']='open';
    }

    $this->validField('post_title','标题不能为空');
    //相同标题是否已发布
    $title_unique = get_option('icollect_title_unique', false);
    $this->checkUnique($title_unique);
    $this->checkCategory($this->getParam('post_category'));//校验分类
    $this->checkUser($this->getParam('post_author'));//校验用户id

    //发布日期
    date_default_timezone_set(get_option('timezone_string')); //设置时区
    if ($this->getParam('post_date')>0) {
      $this->setParam('post_date',date("Y-m-d H:i:s", $this->getParam('post_date')));
    } else {
      $this->setParam('post_date',date("Y-m-d H:i:s", time()));
    }

    $this->setParam('post_content',addslashes($this->getParam('post_content')));//转义
  }

  function post(){
    global $wp_error;

    $this->checkPassword();

    function thumbHandler($image_url,$post_id){
      if (!empty($post_id) && !empty($image_url)) {
        $image_url_final = $image_url;

        if (substr($image_url, 0, 2) === "//") {
          $image_url_final = 'http:' . $image_url;
        } else if (strpos($image_url, '/') === 0) {
          $image_url_final = get_home_url() . $image_url;
        }
        $upload_dir = wp_upload_dir();
        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        $image_data = file_get_contents($image_url_final, false, stream_context_create($arrContextOptions));
        if(!$image_data){
          throw new Exception('获取图片'.$image_url_final.'失败，请检查图片地址是否正确且存在');
        }

        $suffix = "jpg";
        $filename = md5($image_url_final) . "." . $suffix;
        if (wp_mkdir_p($upload_dir['path'])) {
          $file = $upload_dir['path'] . '/' . $filename;
        } else {
          $file = $upload_dir['basedir'] . '/' . $filename;
        }

        file_put_contents($file, $image_data);
        if (file_exists($file)) {
          $wp_filetype = wp_check_filetype($filename, null);
          $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit',
          );
          // attachment相关
          $attach_id = wp_insert_attachment($attachment, $file, $post_id);
          require_once ABSPATH . 'wp-admin/includes/image.php';
          $attach_data = wp_generate_attachment_metadata($attach_id, $file);
          wp_update_attachment_metadata($attach_id, $attach_data);
          set_post_thumbnail($post_id, $attach_id);
        }
      }
    }

    try {

      $this->handlerParams();

      //写入数据库
      kses_remove_filters();
      $post_id = wp_insert_post($this->getParams(),$wp_error);
      kses_init_filters();

      if (empty($post_id)) {
        $this->error('PUBLIC_FAIL', "post_id is Empty", "文章插入失败");
      }

      $this->downloadImages();//图片下载

      //缩略图处理
      $image_url = $this->getParam('feature_url');
      thumbHandler($image_url,$post_id);

      //其它meta数据处理
      if (!empty($post_id)) {
        foreach ($_POST as $key => $value) {
          if (strpos($key, 'extra_') === 0) {
            $real_name = sanitize_text_field(substr($key, 6));
            if (!empty($real_name)) {
              update_post_meta($post_id, $real_name, sanitize_text_field($value));
            }
          }
        }
      }
      
      $this->success(array(
        "url" => get_home_url() . "/?p=" . $post_id,
        'data'=>$this->getParams(),
      ));
    }catch(Exception $e) {
      $this->error('PUBLIC_FAIL', 'public fail', $e->getMessage());
    }
  }
}

if (is_admin()) {
  //将函数连接到添加菜单
  add_action('admin_menu', 'icollect_add_menu');
}

//在后台管理界面添加菜单
function icollect_add_menu() {
  if (function_exists('add_menu_page')) {
    add_menu_page('爱采集', '爱采集', 'administrator', 'icollect/setting.php', '', plugins_url('icollect/logo-20.png'));
  }
}
add_action('init', 'icollect_public');
function icollect_myplugin_activate() {
}
// 寄存一个插件函数，该插件函数在插件被激活时运行
register_activation_hook(__FILE__, 'icollect_myplugin_activate');

function icollect_public(){
  global $wp_version;
  $version=array(
    'php' => PHP_VERSION,
    'plugin' => '1.0.0',
    'wp' => $wp_version,
  );
  $_icollectWordpressAutoPost= new IcollectWordpressAutoPost($version);  
}

?>