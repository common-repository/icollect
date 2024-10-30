<?php
require_once 'Utils.php';

$blogtitle='爱采集发布插件';

/**
保存数据
*/
function getConfig($key,$default=''){
  $data=get_option($key, $default);
  if (empty($data)){
    $data=$default;
  }
  return $data;
}
$icollect_config_password='icollect_password';
$icollect_config_title_unique='icollect_title_unique';

// 接收参数
$param_save=isset($_POST['save'])?sanitize_text_field($_POST['save']):false;

if ($param_save) {
  $icollect_password=isset($_POST[$icollect_config_password])?sanitize_text_field($_POST[$icollect_config_password]):'';
  $icollect_title_unique=isset($_POST[$icollect_config_title_unique])?sanitize_text_field($_POST[$icollect_config_title_unique]):false;
  update_option('icollect_password', $icollect_password);
  update_option('icollect_title_unique', $icollect_title_unique);
  $message= '<div id="message" class="updated fade"><p>保存成功</p></div><script>setTimeout(function(){document.querySelector("#message").style.display="none";},3e3)</script>';
}else{
  $icollect_password=getConfig($icollect_config_password,'icollect');
  $icollect_title_unique=getConfig($icollect_config_title_unique,false);
}

$domain=IcollectUtils::getHomeUrl();

$checked=$icollect_title_unique == true ?"checked='checked'":'';

$datas=array(
  '采集发布设置'=>array(
    '网站发布地址'=>"<input type=\"text\" id=\"homeUrl\" name=\"homeUrl\" class=\"icollect-config\" readonly value=\"{$domain}\" />（采集和发布数据下载 <a href=\"http://www.icollect.net.cn\" target=\"_blank\">爱采集采集软件</a>）",
    '发布密码'=>"<input type=\"text\" name=\"{$icollect_config_password}\" class=\"icollect-config\" value=\"{$icollect_password}\" />（请注意修改并保管好,到 <a href=\"http://www.icollect.net.cn\" target=\"_blank\">爱采集采集软件</a>发布需要用到）",
    '根据标题去重'=>"<input type=\"checkbox\" name=\"{$icollect_config_title_unique}\" value=\"true\" {$checked} />存在相同标题，则不插入",
    '  '=>'<button type="submit"  name="save"  value="1" class="button-primary">保存更改</button>',
  ),
  '说明'=>array(
    '爱采集官网'=>'<a href="http://www.icollect.net.cn" target="_blank">www.icollect.net.cn</a> &nbsp;&nbsp;&nbsp;&nbsp;QQ交流群：1006321295',
    '采集和发布教程'=>'<a href="http://www.icollect.net.cn/docs" target="_blank" title="帮助说明">帮助说明</a>
                      <a href="https://note.youdao.com/coshare/index.html?token=3F33F9D926AB4A2DA225E7BA995B2209&gid=65488663" target="_blank">数据发布到 Wordpress 教程</a>',
    '平台主要功能特性'=>'<p>简单易用，点点鼠标抓取数据，不需要写任何代码就能轻松抓取数据，老板再也不用逼我学 python 爬虫了。几分钟就能抓取数据，采集从未如此轻松。</p>
                    <p>可见即可得,采集结果实时展示。可视化采集过程，看到什么就采集什么。</p>
                    <p>功能强大,支持图片、视频抓取，延迟加载数据页面，滚动翻页，存储过程，更多高级功能等你来发现。</p>',
  ),
);

wp_enqueue_style('icollect_css', plugin_dir_url(__FILE__).'assets/style.css');

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>爱采集发布插件</title>
</head>
<body>

<?php 
echo $message;
?>
<div class="icollect-config-form">
  <div class="header"><?php echo $blogtitle;?></div>
  <div class="icollect-setting-page">
    <form id="edit" name="edit" method="post" action="">
      <?php if (function_exists('CheckIsRefererValid')) {
          echo '<input type="hidden" name="csrfToken" value="' . $zbp->GetCSRFToken() . '">';
      }?>
      <div>
        <p class="pa-1">
          <a href="http://www.icollect.net.cn" target="_blank">爱采集</a>是一个超易用，强大的网页数据采集和发布软件
        </p>
        <?php 
          function trCode($key,$value){
            return "
                    <tr>
                      <td width='15%'>{$key}</td>
                      <td>{$value}</td>
                    </tr>
                    ";  
          }
          foreach ($datas as $key => $data) {
            $tr='';
            foreach ($data as $key2 => $value) {
              $tr=$tr.trCode($key2,$value);
            }
            echo "
                  <div class='pt-2'>
                    <h3>{$key}</h3>
                    <div>
                      <table width='99%' class='icollect-config-table' cellpadding='0' cellspacing='0'>
                        {$tr}
                      </table>
                    </div>
                  </div>
                  ";
          }?>
      </div>
    </form>   
  </div>
</div>
<?php
  wp_nonce_field('icollect_message');
?>  


</body>
</html>
