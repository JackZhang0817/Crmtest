<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>跳转提示</title>
<link href="__PUBLIC__/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
<style type="text/css">
*{ padding: 0; margin: 0; }
body{ background:#fff; font-family:'微软雅黑'; color:#333; font-size:16px;}
.system-message{ padding:40px 20px; width:400px; margin:200px auto; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; border: 1px solid #ABC0DB; background: #E4ECF5; font-family:"Microsoft Yahei"; font-size:14px;-moz-box-shadow:4px 4px 3px rgba(20%,20%,40%,0.5); -webkit-box-shadow:4px 4px 3px rgba(20%,20%,40%,0.5); box-shadow:4px 4px 3px rgba(20%,20%,40%,0.5); position: relative;}
.system-message > .title{ font-size:14px; font-weight:normal; margin-bottom:12px; position:absolute; top:0px; left:10px; border:#fff solid 1px; border-top:none; padding:3px 10px; background:#ABC0DB; color:#fff;}
.system-message span{ font-size:70px; font-weight:normal; margin:-5px 20px 0px 0px; color:green; float:left;}
.system-message .jump{ padding-top:10px; margin-left:75px;}
.system-message .jump a{ color:green;}
.system-message .success,.system-message .error{ line-height:1.2em; font-size:25px }
.system-message .detail{ font-size:12px; line-height:20px; margin-top:12px; display:none}
</style>
</head>
<body>
<div class="system-message">
<a class="title">提示信息</a>
<present name="message">
<span><i class="fa fa-check"></i></span>
<p class="success"><?php echo($message); ?></p>
<else/>
<span><i class="fa fa-times"></i></span>
<p class="error"><?php echo($error); ?></p>
</present>
<p class="detail"></p>
<p class="jump">
<b id="wait"><?php echo($waitSecond); ?></b>秒后自动跳转, 如页面没有跳转 <a id="href" href="<?php echo($jumpUrl); ?>">点击我</a>
</p>
</div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time <= 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>