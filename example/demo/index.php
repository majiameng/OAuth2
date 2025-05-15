<?php
error_reporting(0);
session_start();
@header('Content-Type: text/html; charset=UTF-8');

?><!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
  <meta name="renderer" content="webkit"/>
  <title>Oauth2登录SDK</title>
  <link href="//lib.baomitu.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container">
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">
<?php if (isset($_SESSION['user'])) {?>
<div class="panel panel-success">
	<div class="panel-heading" style="text-align: center;"><h3 class="panel-title">
		登录成功
	</h3></div>
	<div class="panel-body">
		<div class="list-group">
            <?php foreach ($_SESSION['user'] as $key => $value):?>
                <div class="form-group">
                    <label><?php echo $key;?>：</label>
                    <?php if(is_array($value)):?>
                        <textarea  rows="5" class="form-control" readonly="readonly">
                            <?php echo json_encode($value); ?>
                        </textarea>
                    <?php else:?>
                        <input type="text" value="<?php echo $value; ?>" class="form-control" readonly="readonly"/>
                    <?php endif;?>
                </div>
            <?php endforeach;?>
		</div>
	</div>
</div>
<?php }?>
<div class="panel panel-info">
	<div class="panel-heading" style="text-align: center;"><h3 class="panel-title">
		微梦聚合登录SDK
	</h3></div>
	<div class="panel-body" style="text-align: center;">
		<form action="./connect.php" method="get" role="form">
		<div class="list-group">
			<div class="form-group">
			<div class="input-group"><div class="input-group-addon">登录方式</div>
			  <select name="type" class="form-control">
			    <option value="qq">QQ快捷登录</option>
                <option value="wechat">微信快捷登录</option>
                <option value="douyin">抖音快捷登录</option>
                <option value="alipay">支付宝快捷登录</option>
                <option value="aliyun">阿里云快捷登录</option>
                <option value="sina">新浪微博快捷登录</option>
                <option value="github">GitHub快捷登录</option>
                <option value="baidu">百度快捷登录</option>
                <option value="coding">Coding快捷登录</option>
                <option value="csdn">CSDN快捷登录</option>
                <option value="gitee">Gitee快捷登录</option>
                <option value="gitlab">GitLab快捷登录</option>
                <option value="oschina">OSChina快捷登录</option>
                <option value="google">Google快捷登录</option>
                <option value="facebook">Facebook快捷登录</option>
                <option value="naver">Naver快捷登录</option>
                <option value="twitter">Twitter快捷登录</option>
                <option value="line">Line快捷登录</option>
              </select>
			</div>注：Google、facebook、twitter等这些国外平台需要海外或者HK服务器才能回调成功</div>
		</div>
		<button type="submit" class="btn btn-default btn-block">提交</button>
		</form>
	</div>
</div>
</div>
</div>
</body>
</html>