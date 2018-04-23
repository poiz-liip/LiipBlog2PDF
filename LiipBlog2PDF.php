<?php

/**
 * Plugin Name: Liip Blog 2 PDF
 * Plugin URI:  http://liip.ch
 * Description: A Plugin that enables Editors to export Blog-Posts to PDF at the click of a Button. The Functionality also takes into Account the express addition of Image Alt-Text to the generated PDF...
 * Version:     1.0.0
 * Author:      Liip AG
 * Author URI:  http://liip.ch
 * Text Domain: lam
 */

define( 'LB2PDF_VERSION' ,     '1.0.0' );
define( 'LB2PDF_PLUGIN_FILE',  __FILE__ );
define( 'LB2PDF_PLUGIN_URL',   untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'LB2PDF_PLUGIN_DIR',   dirname( __FILE__ ) );


require_once __DIR__ . "/vendor/autoload.php";

use Liip\Helper\Facilitator as HELPER;

# INSTANTIATE A NEW Liip\Helper\Facilitator OBJECT
$pdfHelper      = new HELPER();

if ( isset( $_GET['task'] ) && isset( $_GET['pid'] ) ) {
	# IF THE QUERY PARAM 'task' IS SET TO 'pdf'
	if($_GET['task'] == 'pdf') {
		# AND WE HAVE A POST (BASED ON THE QUERY PARAM: pid), WE CAN PROCEED WITH PDF GENERATION...
		if ( $post = get_post( $_GET['pid'] ) ) {
			$arrHTMLData                = $pdfHelper->getHTMLMarkupForPDFRendering($post);
			$arrHTMLData['permalink']   = get_permalink($post);

			# GENERATE AND SAVE THE PDF FILE....
			$pdfHelper->generateAndSavePDF($arrHTMLData, true);
		}
	}
}

if( is_admin() ) {
    add_action('media_buttons', [$pdfHelper, 'addGeneratePDFButton']);
}


// REGISTER PLUGIN ACTIVATION HOOK
register_activation_hook(LB2PDF_PLUGIN_FILE,    [$pdfHelper, 'activateLB2PDFPlugin']);

// REGISTER PLUGIN DEACTIVATION HOOK
register_deactivation_hook(LB2PDF_PLUGIN_FILE,  [$pdfHelper, 'deactivateLB2PDFPlugin']);
