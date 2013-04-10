<?php
$class = array();
$class[$_GET['action']] = ' class="active"';
?>
	<div class="grid_11" id="scaffold">
		<h2><?=ucfirst(str_replace('_', ' ', i18n($_GET['controller']))).' :: '.TXT_EDIT ?></h2>
		
		<ul class="menu">
<?php if (PK_Acl::isAllowed('', array($_GET['controller'], 'new'))) { ?>
			<li<?php echo $class['new'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=new') ?>"><?= TXT_NEW ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'index'))) {
?>
			<li<?php echo $class['index'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller']) ?>"><?= TXT_LIST ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'details'))) {
?>
			<li<?php echo $class['edit'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=details&id='.$_GET['id']) ?>"><?= TXT_DETAILS ?></a></li>
<?php
}
if (PK_Acl::isAllowed('', array($_GET['controller'], 'delete'))) {
?>
			<li<?php echo $class['delete'] ?>><a href="<?php url('?plugin='.$_GET['plugin'].'&controller='.$_GET['controller'].'&action=delete&id='.$_GET['id']) ?>"><?= TXT_DELETE ?></a></li>
<?php } ?>
			<li class="clear"></li>
		</ul>
		
		<div class="clear"></div>
		
		<div>&nbsp;</div>
		
		<form method="post" enctype="multipart/form-data" action="<?= $row_Template['form_action'] ?>">
			<?php
			foreach ($row_Template['form'] as $row_Form) {
				$row_Form['param']['class'] = 'text';
				PK_Form::create($row_Form);
			}
			?>
			<div class="clear"></div>
		</form>
	</div>