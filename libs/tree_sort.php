<?php

/* Пример вызова функции из шаблона:
 *
 * {treesort content=$mainmenu result=mainmenu order=title visible=show_subs sub=sub desc=0}
 *
 * content - что сортируем
 * order - по значению какого ключа сортируем
 * visible - имя ключа, по которому учитываем необходимость обработки вложенного массива (необязательно, по умолчанию false)
 * sub - имя ключа вложенного массива (необязательно, по умолчанию 'sub')
 * desc - обратный порядок сортировки (необязательно, по умолчанию false)
 */

function prepareTreeSort( $params )
{

	if( empty($params['content']) )
		return $params['content'];

	$order   = $params['order'];
	$desc    = isset($params['desc']) ? $params['desc'] : false;
	$visible = isset($params['visible']) ? $params['visible'] : false;
	$sub     = isset($params['sub']) ? $params['sub'] : 'sub';

	$tst = new treeSortTestObj($order, $desc);

	if( is_array( $params['content'] ) )
		usort( $params['content'], array( $tst, 'compare' ) );

	if( is_array( $params['content'] ) )
		foreach( $params['content'] as &$value ) {

			if( (($visible and $value[$visible]) or !$visible) and $value[$sub] )
				$value[$sub] = prepareTreeSort( array( 'order' => $order, 'sub' => $sub, 'visible' => $visible, 'content' => $value[$sub] ) );
		}

	return $params['content'];
}

class treeSortTestObj
{

	protected $order, $desc;

	public function __construct( $order, $desc = false )
	{

		$this->order = $order;
		$this->desc  = $desc;
	}

	public function compare( $a, $b )
	{

		if( $a[$this->order] == $b[$this->order] )
			return 0;

		return (($a[$this->order]>$b[$this->order]) xor $this->desc) ? 1 : -1;
	}
}

/* Test

$a = array(

		array(
			'title' => 'О компании',
			'show_subs' => false,
			'sub' => array(
				array( 'title' => 'Миссия', 'show_subs' => false, 'sub' => array() ),
				array( 'title' => 'Контакты', 'show_subs' => false, 'sub' => array() ),
			)
		),

		array(
			'title' => 'Наши новости',
			'show_subs' => false,
			'sub' => array()
		),

		array(
			'title' => 'Масложировая продукция',
			'show_subs' => true,
			'sub' => array(
				array(
					'title' => 'Маргарин',
					'show_subs' => true,
					'sub' => array(
						array( 'title' => 'Домашний', 'show_subs' => false, 'sub' => array() ),
						array( 'title' => '620', 'show_subs' => false, 'sub' => array() )
					)
				),
				array(
					'title' => 'Жир кондитерский',
					'show_subs' => false,
					'sub' => array(
						array( 'title' => 'Первый сорт', 'show_subs' => false, 'sub' => array() ),
						array( 'title' => 'Высший сорт', 'show_subs' => false, 'sub' => array() )
					)
				),
			)
		)
);

$time = microtime(true);
echo '<html><head></head><body><pre>';
print_r( prepareTreeSort( array( 'content' => $a, 'order' => 'title', 'visible' => 'show_subs' ) ) );
echo '</pre><p>' . ( microtime(true) - $time ) . '</p></body></html>';
*/

?>
