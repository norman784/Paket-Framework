<?php
class PK_Component_Tab extends PK_Component
{
	function create_js($id, $tab_options)
	{
		$output = '$("#'.$id.' #spinner").hide();
		
$("#'.$id.'").tabs({
	fx: {
		opacity: "toggle"
	},
	spinner: "Cargando..",
	select: function(event, ui) {
		$("#'.$id.' .ui-tabs-panel").hide();
		$("#'.$id.' #spinner").fadeIn();
	},
	show: function(event, ui) {
		$("#'.$id.' #spinner").fadeOut();
	},
	load: function(event, ui) {
		$("#'.$id.' #spinner").fadeOut();
	}
});';
		$output .= '';
		
//		foreach($tab_options as $option):
//			switch($option['method']):
//			case 'ajax':
//				$output .= '$(#"'.$option['id'].'").click();';
//				break;
//			endswitch;
//		endforeach;
		
		return $output;
	}
	
	function create_html($id, $tab_options)
	{
		$tab = '';
		$container = '';
		
		$container .= '<div id="spinner"></div>';
		
		foreach ($tab_options as $option):
//			echo $option['title'].'<br>'.$option['content'].'<hr>';
			switch($option['method']):
			case 'ajax':
				$tab .= '<li><a href="'.$option['content'].'" title="'.$id.'_ajax_request"><span>'.$option['title'].'</span></a></li>'."\n";
//				$tab .= '<li><a href="'.$option['content'].'"><span>'.$option['title'].'</span></a></li>'."\n";
				
				if (false === strpos($container, $id.'_ajax_request'))
					$container .= '<div id="'.$id.'_ajax_request"></div>'."\n";
				
				break;
			default:
				$option['id'] = str_replace(' ', '_', $option['title']);
//				$tab .= '<li><a href="#'.$option['id'].'" title="'.$option['id'].'"><span>'.$option['title'].'</span></a></li>'."\n";
				$tab .= '<li><a href="#'.$option['id'].'" title="'.$option['title'].'"><span>'.$option['title'].'</span></a></li>'."\n";
				
				if (false === strpos($container, $option['id']))
					$container .= '<div id="'.$option['id'].'">'.$option['content'].'</div>'."\n";
				
				break;
			endswitch;
		endforeach;
		
		$output = "<div id=\"".$id."\">\n<ul>\n".$tab."</ul>\n".$container."</div>";
		
		return $output;
	}
}
?>