<html>
<head>
	<style>
		form,fieldset{border:0;padding:0;margin:0}
		textarea{width:100%;height:450px;margin:0;padding:0}
		p{text-align:center;padding:7px 0 0 0;margin:0}
	</style>
</head>
<body>
<?php
if ( ! empty( $_POST['text'] ) ) {

	include dirname( __FILE__ ) . '/typograf.php';

	$t = new TypografWebService;

	if ( ! empty( $_POST['type'] ) )
		$t->setLebedevService();

	$result = $t->request( $_POST['text'] );
}
?>
	<form action="" method="post" id="typograf-dialog-form">
		<fieldset>
			<textarea id="typograf-dialog-area" cols="" rows="" name="text"><?php if ( ! empty( $result ) ) echo htmlspecialchars( $result ); ?></textarea>
			<?php if ( empty( $result ) ) echo '<p><img src="http://src.sitko.ru/3.0/i/ajax-loader.gif"></p>'; ?>
		</fieldset>
	</form>
</body>
</html>
