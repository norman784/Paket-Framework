<?php
class PK_Tag
{
	function cloud($items = array()) {
		global $data;
		
		if (!is_array($items))
			$items = array($items);
		
		$terms = array(); // create empty array
		$data['maximum'] = 0; // $maximum is the highest counter for a search term
		
		foreach ($items as $item):
			$key = key($item);
			
			$term = $item[$key[0]];
		    $counter = $row[$key[1]];
		    // update $maximum if this term is more popular than the previous terms
		    if ($counter> $data['maximum']) $data['maximum'] = $counter;
		    $terms[] = array('title' => $term, 'counter' => $counter);
		endforeach;
		
//		$query = mysql_query("SELECT term, counter FROM search ORDER BY counter DESC LIMIT 30");
//		
//		while ($row = mysql_fetch_array($query)):
//		    $term = $row['term'];
//		    $counter = $row['counter'];
//		    // update $maximum if this term is more popular than the previous terms
//		    if ($counter> $maximum) $maximum = $counter;
//		    $terms[] = array('term' => $term, 'counter' => $counter); 
//		endwhile;
		
		// shuffle terms unless you want to retain the order of highest to lowest
		shuffle($terms);
		
		return $terms;
	}
}
?>