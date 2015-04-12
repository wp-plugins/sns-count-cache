

<?php

	if ( $messages = get_option( self::OPT_COMMON_ERROR_MESSAGE  ) ) {
?>
	<div class="error">
	  		<ul>
			  	<?php
	  				foreach( $messages as $message ) {
				?>
			  		<li><?php echo esc_html( $message ); ?></li>
			  	<?php
					}
	  			?>
	  		</ul>
	</div>
<?php
	  	delete_option( self::OPT_COMMON_ERROR_MESSAGE );
	}
?>