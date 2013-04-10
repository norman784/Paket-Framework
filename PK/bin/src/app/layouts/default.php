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

<title>Paket Framework</title>

<?php echo $row_Template['meta']['scaffolding'] ?>

</head>

<body>

	<h1>Paket Framework example app</h1>

	<?php $this->render('view') ?>
	
	<?php PK_debug('', '', 'output') ?>

</body>
</html>
