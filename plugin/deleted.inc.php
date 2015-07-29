<?php
// PukiWiki - Yet another WikiWikiWeb clone
// $Id: deleted.inc.php,v 1.6 2005/01/22 04:22:01 henoheno Exp $
//
// Show deleted (= Exists in BACKUP_DIR or DIFF_DIR but not in DATA_DIR)
// page list to clean them up
//
// Usage:
//   index.php?plugin=deleted[&file=on]
//   index.php?plugin=deleted&dir=diff[&file=on]

function plugin_deleted_action()
{
	global $vars;
	$qm = get_qm();
	
	$dir = isset($vars['dir']) ? $vars['dir'] : 'backup';
	$withfilename  = isset($vars['file']);

	$_DIR['diff'  ]['dir'] = DIFF_DIR;
	$_DIR['diff'  ]['ext'] = '.txt';
	$_DIR['backup']['dir'] = BACKUP_DIR;
	$_DIR['backup']['ext'] = BACKUP_EXT; // .gz or .txt

	if (! isset($_DIR[$dir]))
		return array('msg'=>$qm->m['plg_deleted']['title_err'], 'body'=> $qm->m['plg_deleted']['err_no_setting']);

	$deleted_pages  = array_diff(
		get_existpages($_DIR[$dir]['dir'], $_DIR[$dir]['ext']),
		get_existpages());

	if ($withfilename) {
		$retval['msg'] = $qm->m['plg_deleted']['title_withfilename'];
	} else {
		$retval['msg'] = $qm->m['plg_deleted']['title'];
	}
	$retval['body'] = page_list($deleted_pages, $dir, $withfilename);

	return $retval;
}
?>
