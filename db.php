<?php
/*
 * A mess
 */
require_once "dbeng/db_sqlite3.php";
require_once "dbeng/db_mysql.php";

class db
{
	private $_conn;
	
    function __construct($db)
    {
		global $settings;
		
		switch ($db['engine']) {
			case 'sqlite3'	: $this->_conn = new db_sqlite3($db['path']);
							  break;
							  
			case 'mysql'	: $this->_conn = new db_mysql($db['host'],
												$db['user'],
												$db['pass'],
												$db['dbname']); 
							  break;
							  
		    default			: die("Unknown DB engine specified, please choose sqlite3 or mysql");
		} # switch
    } # ctor
	
	function setMaxArticleId($server, $maxarticleid) {
		return $this->_conn->exec("REPLACE INTO nntp(server, maxarticleid) VALUES('%s',%s)", Array($server, (int) $maxarticleid));
	} # setMaxArticleId

	function getMaxArticleId($server) {
		$p = $this->_conn->singleQuery("SELECT maxarticleid FROM nntp WHERE server = '%s'", Array($server));
		
		if ($p == null) {
			$this->setMaxArticleId($server, 0);
			$p = 0;
		} # if
		
		return $p;
	} # getMaxArticleId

	function getSpotCount() {
		$p = $this->_conn->singleQuery("SELECT COUNT(1) FROM spots");
		
		if ($p == null) {
			return 0;
		} else {
			return $p;
		} # if
	} # getSpotCount

	function getSpots($id, $limit, $sqlFilter) {
		$results = array();

		if (!empty($sqlFilter)) {
			$sqlFilter = ' AND ' . $sqlFilter;
		} # if
		
		return $this->_conn->arrayQuery("SELECT * FROM spots WHERE id > " . (int) $id . $sqlFilter . " ORDER BY stamp DESC LIMIT " . (int) $limit);
	} # getSpots
	
	function getSpot($messageId) {
		return $this->_conn->arrayQuery("SELECT * FROM spots WHERE messageid = '%s'", Array($messageId));
	} # getSpot()

	function beginTransaction() {
		$this->_conn->exec('BEGIN;', array());
	} # beginTransaction

	function abortTransaction() {
		$this->_conn->exec('ABORT;', array());
	} # abortTransaction
	
	function endTransaction() {
		$this->_conn->exec('COMMIT;', array());
	} # endTransaction
	
	function safe($q) {
		return $this->_conn->safe($q);
	} # safe
	
	function addCommentRef($messageid, $revid, $nntpref) {
		return $this->_conn->exec("REPLACE INTO commentsxover(messageid, revid, nntpref) 
								   VALUES('%s', %d, '%s')",
								Array($messageid, (int) $revid, $nntpref));
	} # addCommentRef
	
	function getCommentRef($nntpref) {
		return $this->_conn->arrayQuery("SELECT messageid, MAX(revid) FROM commentsxover WHERE nntpref = '%s' GROUP BY messageid", Array($nntpref));
	} # getCommentRef

	function addSpot($spot) {
		return $this->_conn->exec("INSERT INTO spots(spotid, messageid, category, subcat, poster, groupname, subcata, subcatb, subcatc, subcatd, title, tag, stamp) 
				VALUES(%d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				 Array($spot['ID'],
					   $spot['MessageID'],
					   $spot['Category'],
					   $spot['SubCat'],
					   $spot['Poster'],
					   $spot['GroupName'],
					   $spot['SubCatA'],
					   $spot['SubCatB'],
					   $spot['SubCatC'],
					   $spot['SubCatD'],
					   $spot['Title'],
					   $spot['Tag'],
					   $spot['Stamp']));
	} # addSpot()
	
}

