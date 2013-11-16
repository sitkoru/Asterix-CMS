<?php

class acmsWatermark
{

	public static function setWatermark( $src_image_path, $field_sid = false )
	{
		// В интерфейсе отменили установку watermark
		if( $field_sid )
			if( IsSet( $_POST[ $field_sid . '_watermark_notset' ] ) )
				return false;

		// Проверяем файл, который планируем изменять
		if( !is_writable( $src_image_path ) )
			return false;

		// Проверяем, доступен ли файл watermark
		$watermark_file = model::$settings[ 'watermark_file' ][ 'path' ];
		if( !is_readable( model::$config[ 'path' ][ 'www' ] . $watermark_file ) )
			return false;

		// Куда устанавливаем watermark
		$watermark_side = self::getWatermark_how();

		// Устанавливаем watermark
		$images_lib_path = model::$config[ 'path' ][ 'libraries' ] . '/acmsImages.php';
		if( is_readable( $images_lib_path ) ) {
			require_once( $images_lib_path );
			$acmsImages = new acmsImages();

			return $acmsImages->put_watermark( $src_image_path, false, model::$config[ 'path' ][ 'www' ] . $watermark_file, $watermark_side );
		}

		return false;
	}

	public static function getWatermark_how()
	{
		$allowed_vals = array( 'rt', 'rb', 'lt', 'lb', 'cc' );

		if( IsSet( model::$settings[ 'watermark_how' ] ) )
			if( in_array( strval( model::$settings[ 'watermark_how' ] ), $allowed_vals ) )
				return strval( model::$settings[ 'watermark_how' ] );

		return 'rb';
	}

	public static function getSettings()
	{

		$settings = array(
			/* водяные знаки */
			'watermark_on'       => array(
				'group' => 'Водяной знак',
				'title' => 'Включить установку "водяного знака" на фотографии',
				'type'  => 'check',
			),
			'watermark_custom'   => array(
				'group' => 'Водяной знак',
				'title' => 'Оставить возможность отказаться от установки водяного знака',
				'type'  => 'check',
			),
			'watermark_where'    => array(
				'group'    => 'Водяной знак',
				'title'    => 'Куда устанавливать водяной знак',
				'type'     => 'menum',
				'variants' => array(
					'image'     => 'На картинки, загружаемые в поля отдельных изображений в админке',
					'gallery'   => 'На картинки, загружаемые в поля фотогалерей в админке',
					'upload'    => 'На картинки, загружаемые через визуальный редактор',
					'interface' => 'На картинки, загружаемые пользователем через сайт (кроме аватарок)',
				),
			),
			'watermark_how'      => array(
				'group'    => 'Водяной знак',
				'title'    => 'Как устанавливать водяной знак',
				'type'     => 'menu',
				'variants' => array(
					'lt' => '↖ Верхний левый угол',
					'rt' => '↗ Верхний правый угол',
					'rb' => '↘ Нижний правый угол',
					'lb' => '↙ Нижний левый угол',
					'cc' => '· В центре картинки',
				),
			),
			'watermark_file'     => array(
				'group' => 'Водяной знак',
				'title' => 'Файл водяного знака',
				'type'  => 'file',
			),
			'watermark_min_size' => array(
				'group'  => 'Водяной знак',
				'title'  => 'Минимальный размер картинки, на которые нужно ставить водные знаки (px)',
				'type'   => 'int',
				'defaut' => 350,
			),
		);

		return $settings;
	}

	public static function getImageMinSize()
	{
		if( IsSet( model::$settings[ 'watermark_min_size' ] ) )
			if( intval( model::$settings[ 'watermark_min_size' ] ) )
				return intval( model::$settings[ 'watermark_min_size' ] );

		return 350;
	}


	public static function isWatermarkNeeded_image()
	{
		return self::isType( 'image' ) && self::isController_admin();
	}

	public static function isWatermarkNeeded_gallery()
	{
		return self::isType( 'gallery' ) && self::isController_admin();
	}

	public static function isWatermarkNeeded_upload()
	{
		if( IsSet( $_SESSION['watermark_upload_notset'] ) )
			return $_SESSION['watermark_upload_notset'];
		else
			return self::isType( 'upload' );
	}

	public static function isWatermarkNeeded_interface()
	{
		return self::isType( 'interface' ) && !self::isController_admin();
	}

	public static function isWatermark_allowNotSet()
	{
		if( IsSet( model::$settings[ 'watermark_custom' ] ) )
			return !!model::$settings[ 'watermark_custom' ];

		return false;
	}


	private static function isType( $where )
	{
		if( self::isWatermark_on() )
			if( in_array( $where, model::$settings[ 'watermark_where' ] ) )
				return true;

		return false;
	}

	private static function isWatermark_on()
	{
		if( IsSet( model::$settings[ 'watermark_on' ] ) )
			if( !!model::$settings[ 'watermark_on' ] )
				if( IsSet( model::$settings[ 'watermark_file' ] ) )
					if( is_readable( model::$config[ 'path' ][ 'www' ] . model::$settings[ 'watermark_file' ][ 'path' ] ) )
						return true;

		return false;
	}

	private static function isController_admin()
	{
		if( IsSet( model::$ask->controller ) )
			if( model::$ask->controller == 'admin' )
				return true;

		return false;
	}

}