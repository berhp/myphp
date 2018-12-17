<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'/>
	<title></title>
</head>
<style type="text/css">
	.div { margin:30px; }
	.content{font-size:38px;}
</style>
<body>

<div class="div">
	<div class="content">
		<p><?php echo $msg ?></p>
	</div>
	<div>
		<p><a id="href" href="<?php echo($jumpUrl); ?>">立即跳转</a> 等待时间： <b id="wait"><?php echo($waitSecond); ?></b>s</p>
	</div>
</div>


<script type="text/javascript">
	(function(){
		var wait = document.getElementById('wait'),href = document.getElementById('href').href;
		var interval = setInterval(function(){
			var time = --wait.innerHTML;
			if(time <= 0) {
				clearInterval(interval);
				<?php if($_is_history_go===true){ echo 'history.go(-1);'; }else{ echo 'location.href = href;'; } ?>
				//location.href = href;
			};
		}, 1000);
	})();
</script>
</body>
</html>