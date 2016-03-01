	<script type="text/javascript" src="https://src.sitko.ru/3.0/j/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="https://src.sitko.ru/3.0/jquery-ui/js/jquery-ui-1.8.17.custom.min.js"></script>
{if $settings.bootstrap}
	<script type="text/javascript" src="https://src.sitko.ru/3.0/bootstrap/bootstrap-alert.js"></script>
	<script type="text/javascript" src="https://src.sitko.ru/3.0/bootstrap/bootstrap-button.js"></script>
	<script type="text/javascript" src="https://src.sitko.ru/3.0/bootstrap/bootstrap-dropdown.js"></script>
	<script type="text/javascript" src="https://src.sitko.ru/3.0/bootstrap/bootstrap-tab.js"></script>
{/if}
	<script type="text/javascript" src="https://src.sitko.ru/3.0/j/panel.js"></script>
	<script type="text/javascript" src="https://src.sitko.ru/3.0/j/j.js"></script>
	<script type="text/javascript" src="https://src.sitko.ru/3.0/j/lightbox.js"></script>
	<script type="text/javascript" src="{$paths.public_javascript}/j.js"></script>

{if IsSet( $settings.block_ie6 )}
{if $settings.block_ie6 == true}
	<!--[if IE 6]>
	<script type="text/javascript">
		location.replace("http://browsehappy.com/");
	</script>
	<![endif]-->
{/if}
{/if}
