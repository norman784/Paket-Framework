<?php $class[$_GET['action']] = ' class="active"' ?>
	<div class="grid_11" id="scaffold">
		<h2><?=ucfirst(str_replace('_', ' ', i18n($_GET['controller'])))?></h2>
		<ul class="menu">
<?php if (PK_Acl::isAllowed('', array($_GET['controller'], 'new'))) { ?>
			<li<?php echo $class['new'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=new') ?>"><?= TXT_NEW ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'index'))) {
?>
			<li<?php echo $class['index'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller']) ?>"><?= TXT_LIST ?></a></li>
<?php } ?>
			<li class="clear"></li>
		</ul>
		
		<div class="clear"></div>
		
		<div>&nbsp;</div>
		
<?php registros(); ?>
		
		<div>&nbsp;</div>
		
<?php if (count($row_Template['lista']) == 0) { ?>
		<p>Actualmente no hay elementos en la lista para mostrar</p>
<?php } else { ?>
		<ul>
<?php
foreach ($row_Template['lista'] as $row_Lista):
	$key = array_keys($row_Lista);
	
	if ($key[0] == $key[1]) {
		$key[1] = $key[2];
	}
?>
			<li><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=details&id='.$row_Lista[$key[0]]) ?>"><?=((!empty($row_Lista[$key[1]]))?$row_Lista[$key[1]]:$row_Lista[$key[0]])?></a></li>
<?php endforeach; ?>
<?php } ?>
		</ul>
		
<?php paginador(); ?>
	</div>