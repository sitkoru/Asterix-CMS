<?php
/*
 *      $ dbtree_recover.php, v 1.0, 2010-05-14 10:09 YEKST, key@kosmatov.su Exp $
 *      $ {changelog} Exp $
 */

$timer = microtime( true );

$host = str_replace( 'www.', '', $_SERVER['HTTP_HOST'] );

$db = new mysqli( $config['db']['system']['host'], $config['db']['system']['user'], $config['db']['system']['password'] );
$db->select_db( $config['db']['system']['name'] );
$db->query( 'set character set utf8' );

$r = $db->query( "select id from domains where host='$host' or host2='$host' or host3='$host' or host4='$host'" );
$r = $r->fetch_object();

if ( empty( $r->id ) ) die( 'Домен не опознан' );
else $domain_id = $r->id;

$table = 'start_rec';
$text = isset( $_GET['correct_all'] ) ? preg_replace( "/[^a-z\d\-_]/", '', $_GET['correct_all'] ) : '0';
$r = $db->query( "select id, sid, url, dep_path_parent parent, left_key, right_key, $text text from $table where domain='|$domain_id|' order by left_key" );

$rows = array();
while ( $value = $r->fetch_object() ) {

	if ( $value->sid == 'index' ) {

			echo "l:{$value->left_key} r:{$value->right_key} cnt:";
			$tree = new Tree( $value->id, $value->sid, $value->text, $text );
	}

	else {

		$value->sid = trim( $value->sid );
		$value->parent = trim( $value->parent );
		if ( isset( $_GET['translate'] ) ) {

			$value->sid = translate( $value->sid );
			$value->parent = translate( $value->parent );
		}

		if ( isset( $_GET['correct'] ) or isset( $_GET['correct_all'] ) ) {

			$value->sid = correct( $value->sid );
			$value->parent = correct( $value->parent );

			if ( isset( $_GET['correct_all'] ) ) {

				preg_match_all( "/href=\"[^\"]+/", $value->text, $matches );
				if ( isset( $matches[0] ) ) {

					$repl = array();
					foreach ( $matches[0] as $url ) {

						$url = mb_substr( $url, 6, mb_strlen( $url, 'utf-8' ), 'utf-8' );
						if ( ! strpos( $url, ':' ) ) {

							$repl[$url] = correct( urldecode( $url ), '/' );
							if ( isset( $_GET['translate'] ) )
								$repl[$url] = translate( $repl[$url], true );
							echo "\n<br /><a href=\"{$value->url}\">{$value->url}</a>: <a href=\"{$repl[$url]}\" target=\"_blank\">{$repl[$url]}</a>";
						}
					}

					$value->text = $db->real_escape_string( strtr( $value->text, $repl ) );
				}
			}
		}

		$rows[] = $value;
	}
}

echo "\n", count( $rows ), "\n";

$tree->insert( $rows );
$tree->index();

if ( isset( $_GET['update'] ) ) {

	$tree->db_update( $db, $table, $domain_id );
	echo "\n<br />Updated.";
}

//print_r( $tree );

class Tree {

	public
		$id,
		$sid,
		$sons = array()
	;

	public function __construct( $id, $sid, $text = 0, $text_field = '' ) {

		$this->id = (int) $id;
		$this->sid = $sid;
		$this->text = $text;
		$this->text_field = $text_field;
	}

	public function insert( $rows ) {

		foreach ( $rows as $key => $value ) {

			if ( $value->parent == $this->sid ) {

				$this->sons[] = new Tree( $value->id, $value->sid, $value->text, $this->text_field );
				unset( $rows[$key] );
			}
		}

		foreach ( $this->sons as $son )
			$son->insert( $rows );
	}

	public function index() {

		$this->keys();
		$this->levels();
		$this->urls();
	}

	public function keys( $key = 0 ) {

		$this->left_key = $key + 1;
		$current_key = $this->left_key;

		foreach ( $this->sons as $i => $son ) {

			$current_key = $son->keys( $current_key );
		}

		return $this->right_key = $current_key + 1;
	}

	public function levels( $level = 1 ) {

		$this->level = $level;

		foreach ( $this->sons as $i => $son )
			$son->levels( $level + 1 );
	}

	public function urls( $parent_url = '' ) {

		if ( isset( $this->url ) )
			return $this->url;

		$this->url = ( $this->sid != 'index' ) ? "$parent_url/{$this->sid}" : '';

		foreach ( $this->sons as $i => $son )
			$son->urls( $this->url );

		return $this->url;
	}

	public function db_update( $db, $table, $domain, $parent = '' ) {

		$text = $this->text ? ", {$this->text_field}='{$this->text}'" : '';
		$q = "update $table set sid='{$this->sid}', dep_path_parent='$parent', left_key={$this->left_key}, right_key={$this->right_key}, tree_level={$this->level}, url='{$this->url}' $text where id={$this->id} and domain='|$domain|'";
		$db->query( $q );

		//echo $q, "\n";

		foreach ( $this->sons as $son )
			$son->db_update( $db, $table, $domain, $this->sid );
	}
}

echo "\n", microtime( true ) - $timer;


function translate( $value, $url = false ) {

	$table = array(
			'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
			'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'ый' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm',
			'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
			'ф' => 'f', 'х' => 'kh', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shc',
			'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', ' ' => '-',
			'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo',
			'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M',
			'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U',
			'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shc',
			'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
	);

	if ( $url )
		$url = '\/\.';
	else $url='';

	$value = strtr( $value, $table );
	$value = preg_replace( "/[^a-z\d\-_$url]/i", '', $value );

	return $value;
}

function correct( $value, $url = true ) {

	if ( $url )
		$url = '\/\.';
	else $url='';

	$value = preg_replace( "/[\040]/", '_', trim( $value ) );
	return preg_replace( "/[^\da-zа-яё_\-$url]/iu", '', $value );
}

/*
$q = "SELECT date, `sql`, user FROM `bkp` where domain='|$domain_id|' order by date desc limit 100";
$r = $db->query( $q );

while ( $obj = $r->fetch_object() ) {

	echo "\n<strong>", $obj->date, " ", $obj->user, "</strong>\n", $obj->sql, "\n";
	//print_r( unserialize( $obj->bkp ) );
}
*/

?>
