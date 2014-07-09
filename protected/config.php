<?php
return array (
  'REWRITE' => 
  array (
  ),
  'APP' => 
  array (
    'DEBUG' => true,
    'LOG_ON' => true,
    'LOG_PATH' => BASE_PATH . 'cache/log/',
    'URL_HTTP_HOST' => '',
    'TIMEZONE' => 'PRC',
    'COOKIE_RANGE' => '',
    'COOKIE_PATH' => '/',
    'COOKIE_PRE' => 'yx_',
    'HTML_CACHE_ON' => false,
    'HTML_CACHE_PATH' => BASE_PATH . 'cache/html_cache/',
    'HTML_CACHE_RULE' => 
    array (
      'default' => 
      array (
        'index' => 
        array (
          'index' => 3000,
        ),
        'search' => 
        array (
          '*' => 3000,
        ),
        'photo' => 
        array (
          '*' => 3000,
        ),
        'page' => 
        array (
          '*' => 3000,
        ),
      ),
    ),
  ),
  'DB' => 
  array (
    'DB_TYPE'        => 'mysql',
    'DB_HOST'        => 'localhost',
    'DB_USER'        => 'fubuorder',
    'DB_PWD'         => 'fubu@order',
    'DB_PORT'        => '3306',
    'DB_NAME'        => 'ordersys',
    'DB_CHARSET'     => 'utf8',
    'DB_PREFIX'      => '',
    'DB_CACHE_ON'    => false,
    'DB_CACHE_PATH'  => BASE_PATH . 'cache/db_cache/',
    'DB_CACHE_TIME'  => 600,
    'DB_PCONNECT'    => false,
    'DB_CACHE_CHECK' => true,
    'DB_CACHE_FILE'  => 'cachedata',
    'DB_CACHE_SIZE'  => '15M',
    'DB_CACHE_FLOCK' => true,
  ),
  'TPL' => 
  array (
    'TPL_TEMPLATE_PATH'   => '',
    'TPL_TEMPLATE_SUFFIX' => '.html',
    'TPL_CACHE_ON'        => false,
    'TPL_CACHE_TYPE'      => '',
    'TPL_CACHE_PATH'      => BASE_PATH . 'cache/tpl_cache/',
    'TPL_CACHE_SUFFIX'    => '.php',
  ),
  'UPLOAD' =>array(
      'FOLDER'             => 'upload', /* 上传文件保存路径 */
      'FILEEXT'         => '',
      'FILEDESC'        => '',
      'SCRIPT'          => url( 'upload/index' ),
      'DATA'            => "{PHPSESSID:'xxx', ajax:1}",
      'FILEQUEUE'       => 'fileQueue',
      'BUTTONTEXT'      => '浏  览',
      'SIZE'            => 1000000, /* 上传附件大小 */
      'MULTI'           => FALSE,   /* 上传多个文件 */
      'AUTO'            => TRUE,    /* 自动哦上传 */
      'NUM'             => 10,      /* 一次上传附件个数 */
      'TYPE'            => 'jpg,bmp,gif,png,mp3,wma,mp4,3gp,apk', /* 也许文件类型 */
      'WATERMARK'       => FALSE,      /* 是否添加水印 */
      'WATERMARK_LOG'   => 'logo.png', /* 水印图片 */
      'WATERMARK_PLACE' => '5', /* 水印位置(1为左下角,2为右下角,3为左上角,4为右上角,默认为右下角) */
      'REMOVE_ORIGIN'   => TRUE,  /* 是否删除原图 */
      'MAXWIDTH'        => '200',      /* 缩略图最大宽度 */
      'MAXHEIGHT'       => '140',     /* 缩略图最大高度 */      
      ),
  'TAG_TYPE'=>array(	//标签类型
				'1'=>'餐厅菜系',
				'2'=>'餐厅用餐目的'
  ),		
  'VERSION'     => '1.00',
  'DATE'        => '20140326',
  'SITENAME'     => '订单管理系统',
  'SITEURL'      => '',
  'KEYWORDS'     => '订单管理',
  'DESCRIPTION'  => '订单管理',
  'TELEPHONE'    => '',
  'EMAIL'        => '',
  'ADDRESS'      => '',
  'COPYRIGHT'    => '',
  'SPOT'         => '',
);