<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es">

<head>
<base href="<?php echo SERVER_URL; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET ?>" />

<?php if (file_exists(PK_ROOT_DIR.'pk-icon.ico')) { ?>
<link rel="shortcut icon" href="pk-icon.ico" />
<?php } ?>

<meta name="keywords" content="<?php echo KEYWORDS ?>" />
<meta name="description" content="<?php echo DESCRIPTION ?>" />

<title><?=((defined('SITE_TITLE'))?SITE_TITLE:'PK Framework')?> :: <?php echo $row_Template['meta']['titulo'] ?></title>

<link rel="stylesheet" type="text/css" media="all" href="pk/css/960.css" />
<link rel="stylesheet" type="text/css" media="all" href="pk/css/reset.css" />
<link rel="stylesheet" type="text/css" media="all" href="pk/css/text.css" />
<link rel="stylesheet" type="text/css" media="all" href="pk/css/main.css" />
<link rel="stylesheet" type="text/css" media="all" href="pk/css/pk.css" />

<link rel="stylesheet" type="text/css" media="all" href="pk/css/forms.css" />

<script type="text/javascript" src="pk/js/jquery.js"></script>

<?=$row_Template['scaffolding']['head']?>

</head>

<body>

<div class="container_12">
	<div class="grid_12">
		<h1><?=((defined('SITE_TITLE'))?SITE_TITLE.' :: ':'')?><?=ucfirst(i18n(str_replace('_', ' ', $_GET['controller'])))?></h1>
		
		<ul class="menu">
<?php
if (isset($_GET['plugin']))
	$controllers = app_get($_GET['plugin'].'/controller', 'plugins');
else
	$controllers = app_get('controller');
asort($controllers);
PK_Acl::init();
$x = 0;

foreach($controllers as $controller):
	if (!PK_Acl::isAllowed('', array($controller, 'index')))
		continue;
		
	if ($x == 0 && empty($_GET['controller'])) {
		$_GET['controller'] = $controller;
		++$x;
	}
?>
			<li><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$controller) ?>"><?=ucfirst(i18n(str_replace('_', ' ', $controller)))?></a></li>
<?php
endforeach;

if ((int)PK_Users::id() > 0) {
?>
			<li><a href="<?php url('?plugin='.$_GET['plugin'].'&action=logout') ?>"><?=TXT_LOGOUT?></a></li>
<?php } ?>
		</ul>
	</div>

<?php $this->render('view') ?>

</div>

<?php PK_debug('', '', 'output') ?>

</body>
</html>