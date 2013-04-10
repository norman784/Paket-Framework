<?php $class[$_GET['action']] = ' class="active"'; ?>
	<div class="grid_11" id="scaffold">
		<h2><?=ucfirst(str_replace('_', ' ', i18n($_GET['controller']))).' :: '.TXT_DETAILS ?></h2>
		
		<ul class="menu">
<?php if (PK_Acl::isAllowed('', array($_GET['controller'], 'new'))) { ?>
			<li<?php echo $class['nuevo'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=new') ?>"><?= TXT_NEW ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'index'))) {
?>
			<li<?php echo $class['index'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller']) ?>"><?= TXT_LIST ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'edit'))) {
?>
			<li<?php echo $class['editar'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=edit&id='.$_GET['id']) ?>"><?= TXT_EDIT ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'delete'))) {
?>
			<li<?php echo $class['eliminar'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=delete&id='.$_GET['id']) ?>"><?= TXT_DELETE ?></a></li>
<?php } ?>
			<li class="clear"></li>
		</ul>
		
		<div class="clear"></div>
		
		<div>&nbsp;</div>
		
<?php if (count($row_Template['lista']) == 0) { ?>
		<p>Actualmente no hay elementos en la lista para mostrar</p>
<?php } else { ?>
		<ul>
<?php
foreach ($row_Template['lista'] as $i=>$row_Lista):
	if (is_numeric($i) || empty($row_Lista))
		continue;
?>
			<li><strong><?php echo ucfirst(str_replace('_',' ', $i)) ?>:</strong> <?php echo $row_Lista ?></li>
<?php endforeach; ?>
<?php } ?>
		</ul>
	</div>