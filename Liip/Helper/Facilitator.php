<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 17.04.18
 * Time: 23:55
 */

namespace Liip\Helper;

use Dompdf\Dompdf   as PDF;
use Dompdf\Options  as OPTS;

class Facilitator {

	public function generateAndSavePDF($outPut, $pdfFileName, $pdfFileURL, $saveHTMLVersion=false){
		############################################
		###  GENERATE THE PDF FILE AND SAVE IT   ###
		############################################
		$opts   = new OPTS();
		$opts->setIsRemoteEnabled(true);

		$dPDF   = new PDF($opts);
		$dPDF->loadHTML( $outPut );
		$dPDF->setPaper( 'A4', 'portrait')
		     ->render();
		$output = $dPDF->output();

		file_put_contents( $pdfFileName, $output );

		if($saveHTMLVersion) {
			file_put_contents( $pdfFileName . ".html", $outPut );
		}

		// REDIRECT TO PDF FILE URL....
		header( "location: {$pdfFileURL}" );
		exit;
	}

	/**
	 * @param $post
	 *
	 * @return array
	 */
	public function getHTMLMarkupForPDFRendering($post){
		$pdfDir         = wp_upload_dir( null, true, false )['basedir'] . "/blog_pdf/";
		if ( ! file_exists( $pdfDir ) ) {
			mkdir( $pdfDir );
		}
		# USING REGEX, EXTRACT THE ALT TEXT AND POSITION IT RIGHT BELOW THE IMAGE
		$pdfFileName    = $pdfDir . $post->post_name . ".pdf";
		$pdfFileURL     = get_site_url() . "/wp-content/uploads/blog_pdf/" . $post->post_name . ".pdf";
		$cssFileURL     = get_template_directory_uri()  . "/style.css";
		$postContent    = @do_shortcode( $post->post_content );
		$rxImagesAlt    = "#(<img.*? alt\s?=(\s?['\"].*?['\"]).*?>)#";
		$outPut         = preg_replace_callback( $rxImagesAlt, function ( $result ) {
			$pixMarkup  = $result[0];
			$altText    = trim(end( $result ), '"\'');

			return "<div style='margin: 10px 0 0 0;'>{$pixMarkup}</div><div style='margin: 10px 0 10px 0;'>
											<p class='img-alt-text' 
											style='background: #e2e2e2;
											font-family: \"europa-1\", \"Lato\", \"Helvetica Neue\", Helvetica, Arial, sans-serif;
										    font-weight: 600;
										    line-height: 1.2;
											border:solid 1px #c9c9c9;
										    padding: 10px 20px;
										    margin: 0;
										    display: inline-block;
										    color: darkred;
										    clear: both;'>
										    <span style='color:darkblue;font-family: \"europa-1\", \"Lato\", \"Helvetica Neue\", Helvetica, Arial, sans-serif;'>Alt. Text:</span>&nbsp;&nbsp;$altText</p>
										</div>";
		}, $postContent );
		$outPut         = "<!DOCTYPE html>
									<html lang='de'>
									<head>
									    <meta charset='UTF-8'>
									    <title>Title</title>
		                                <link rel='stylesheet' href='{$cssFileURL}' media='all' />
									</head>
		                            <body style='padding:1cm;'>
								    <div class='post full-post type-post status-publish format-standard has-post-thumbnail'>{$outPut}</div></body>";

		return [
			'outPut'        => $outPut,
			'pdfFileURL'    => $pdfFileURL,
			'pdfFileName'   => $pdfFileName,
		];


	}

	public function getCurrentPostType() {
		global $post, $typenow, $current_screen;
		//IF WE HAVE A POST, WE JUST EXTRACT THE POST TYPE FROM IT
		if ( $post && $post->post_type ) {
			return $post->post_type;
		}else if($typenow){
			//IF THE GLOBAL: $typenow (WHICH IS SET IN admin.php) IS HAS VALUE INSTEAD; RETURN IT
			return $typenow;
		}else if( $current_screen && $current_screen->post_type){
			// OTHERWISE WE CHECK THE GLOBAL IF THE GLOBAL: $current_screen
			// (WHICH IS SET IN screen.php) HAS VALUE & RETURN ITS post_type PROPERTY INSTEAD
			return $current_screen->post_type;
		}else if( isset( $_REQUEST['post_type'] ) ){
			// FINALLY WE CHECK IF THE post_type KEY (QUERY-STRING) WAS SET IN THE $_REQUEST
			// AND THEN RETURN THE VALUE (SANITIZED).
			return sanitize_key( $_REQUEST['post_type'] );
		}
		# ALL FAILING, WE RETURN NULL
		return null;
	}

	public function addGeneratePDFButton(){
		global $post;
		$curPostType    = $this->getCurrentPostType();
		$target         = "_blank";
		$permalink      = get_permalink($post);
		$permalink     .= ( strstr($permalink, "?") ) ? "&task=pdf" : "?task=pdf";
		$permalink     .= "&pid={$post->ID}";

		if(strtolower($curPostType) === "post"){
			$btn  = '<style type="text/css">';
			$btn .= '.pz-dld-icon.dashicons, .pz-dld-icon.dashicons-before:before{line-height:1.35;}';
			$btn .= '</style>';
			$btn .= '<a class="button button-default" target="' . $target . '" ';
			$btn .= ' href="' . $permalink . '">';
			$btn .= '<span class="dashicons dashicons-download pz-dld-icon"></span>';
			$btn .= 'Export this Post To PDF</a>';
			echo $btn;
		}
	}



	public function activateLB2PDFPlugin(){
		// TODO
	}

	public function deactivateLB2PDFPlugin(){
		// TODO
	}

}