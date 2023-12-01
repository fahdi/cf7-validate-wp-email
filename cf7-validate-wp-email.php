<?php
/**
 * Contact form 7 Validate Email
 *
 *
 * @link
 * @since             2020.11.0
 * @package           cf7_validate_email
 *
 * @wordpress-plugin
 * Plugin Name:       Contact form 7 Validate Email  by checking against wp user
 * Plugin URI:        http://www.fahdmurtaza.com/get-quote
 * Description:       Connect contact forms 7 to remote API using GET or POST.
 * Version:           1.2.0
 * Author:            Fahad Murtaza
 * Author URI:        http://www.fahdmurtaza.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7-validate-email
 * Domain Path:       /languages
 */

add_filter( 'wpcf7_validate', 'gravixar_email_exists', 10, 2 );

function gravixar_email_exists( $result, $tags ) {
	if ( isset( $_POST['_wpcf7'] ) && $_POST['_wpcf7'] != 1315 ) // Only form id 166 will be validated.
	{
		return $result;
	}
	// retrieve the posted email
	$form  = WPCF7_Submission::get_instance();
	$email = $form->get_posted_data( 'your-email' );
	// if already in database, invalidate
	if ( ! email_exists( $email ) ) // email_exists is a WP function
	{
		$result->invalidate( 'your-email', 'The email entered has not been granted access to this material. For access, please contact Sagewood at info@sagewoodam.com' );
	}

	// return the filtered value
	return $result;
}

add_action( 'wp_footer', 'gravixar_add_js' );

function gravixar_add_js() { ?>
    <script type="text/javascript">
      document.addEventListener('wpcf7invalid', function(event){
        jQuery('.wpcf7-not-valid-tip').filter(function(){
          var html = jQuery(this).html();
          var emailPattern = /[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/g;

          var matched_str = jQuery(this).html().match(emailPattern);
          if (matched_str) {
            var text = jQuery(this).html();
            jQuery.each(matched_str, function(index, value){
              text = text.replace(value,
                '<a href=\'mailto:' + value + '\'>' + value + '</a>');
            });
            jQuery(this).html(text);
            return jQuery(this);
          }
        });
      });
    </script>
	<?php
}
