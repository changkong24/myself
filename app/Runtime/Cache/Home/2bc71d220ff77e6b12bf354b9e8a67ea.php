<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zn">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge,chrome=1">
	<title><?php echo ($title); ?></title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width,height=device-height, initial-scale=1">

	<link rel="apple-touch-icon" href="apple-touch-icon.png">
	<!-- Place favicon.ico in the root directory -->

	<link rel="stylesheet" href="app//Public/bootstrap/css/normalize.css">
	<link rel="stylesheet" href="app//Public/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="app//Public/css/main.css">
	<script type="text/javascript" src="app//Public/js/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="app//Public/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	<!-- 头部内容 -->
	<header>
		<div class="container">
			<div class="row">
				<div class="h_text">
					<h1>徐洋</h1>
					<p>前端开发工程师</p>
					<a href="#aboutme" class="btn btn-default btn-lg">个人简介</a>
					<a href="#" class="btn btn-default btn-lg">专业技能</a>
				</div>
			</div>
		</div>
	</header>
	<!-- 导航栏 -->
	<section id="nav" class="">
		<nav class="navbar navbar-default nav-self">
			<div class="container">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
				  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				    <span class="sr-only">Toggle navigation</span>
				    <span class="icon-bar"></span>
				    <span class="icon-bar"></span>
				    <span class="icon-bar"></span>
				  </button>
				  <a class="navbar-brand" href="#aboutme">徐洋</a>
				</div>

				<!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				  	<ul class="nav navbar-nav navbar-right">
					    <li><a href="#">个人简介</a></li>
					    <li><a href="#">我的作品</a></li>
					    <li><a href="#">我的技能</a></li>
					    <li><a href="#">工作经验</a></li>
					    <li><a href="#">联系我</a></li>
				  	</ul>
				</div><!-- /.navbar-collapse -->
			</div><!-- /.container-fluid -->
		</nav>
	</section>
	<section id="aboutme">
		<div class="container">
			<div class="tit text-center">
				<h2>个人简介</h2>
			</div>
		</div>
	</section>
</body>
</html>