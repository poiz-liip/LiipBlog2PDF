<?php
/**
 * Created by PhpStorm.
 * User: poiz
 * Date: 17.04.18
 * Time: 23:55
 */

namespace Liip\Helper;

use Dompdf\Dompdf as PDF;
use Dompdf\Options as OPTS;

class Facilitator {

	/**
	 * GENERATES AND SAVES THE PDF FILE...
	 *
	 * @param $arrRenderData
	 * @param bool $saveHTMLVersion
	 */
	public function generateAndSavePDF( $arrRenderData, $saveHTMLVersion = false ) {
		/**
		 * @var string $outPut
		 * @var string $pdfDIR
		 * @var string $htmlDIR
		 * @var string $dataDIR
		 * @var string $fontsDIR
		 * @var string $permalink
		 * @var string $pdfFileURL
		 * @var string $pdfFileName
		 * @var string $htmlFileName
		 */
		extract( $arrRenderData );
		if ( $saveHTMLVersion ) {
			file_put_contents( $htmlFileName, $outPut );
		}
		$opts = new OPTS();
		// ENABLE ACCESS TO REMOTE ASSETS LIKE IMAGES, CSS, ETC
		$opts->setIsRemoteEnabled( true );
		$opts->setRootDir( __DIR__ . "/../../vendor/dompdf/dompdf" );
		$opts->setIsHtml5ParserEnabled( true );
		$dPDF = new PDF( $opts );
		$dPDF->loadHTML( $outPut );
		$dPDF->setPaper( 'A4', 'portrait' )
		     ->render();
		$output = $dPDF->output();
		file_put_contents( $pdfFileName, $output );

		// REDIRECT TO PDF FILE URL....
		header( "location: {$pdfFileURL}" );
		exit;
	}

	public function getHTMLMarkupForPDFRendering( $post ) {
		/**
		 * @var string $dataDIR
		 * @var string $dataDIR
		 * @var string $pdfDIR
		 * @var string $htmlDIR
		 * @var string $fontsDIR
		 */
		$blogDataDIR = wp_upload_dir( null, true, false )['basedir'] . "/blog_pdf/";
		$assetsData  = [
			'dataDIR'  => $blogDataDIR,
			'pdfDIR'   => $blogDataDIR . "pdf/",
			'htmlDIR'  => $blogDataDIR . "html/",
			'fontsDIR' => $blogDataDIR . "fonts/",
		];
		extract( $assetsData );
		foreach ( $assetsData as $key => $dataDIR ) {
			if ( ! file_exists( $dataDIR ) ) {
				mkdir( $dataDIR, 0777 );
			}
		}
		# USING REGEX, EXTRACT THE ALT TEXT AND POSITION IT RIGHT BELOW THE IMAGE
		$banner        = $this->grabBannerScrap( get_permalink( $post ) );
		$entryMeta     = $this->grabEntryMetaScrap( get_permalink( $post ) );
		$authorProfile = $this->grabAuthorProfileScrap( get_permalink( $post ) );

		$css         = LB2PDF_PLUGIN_URL . "/resources/css/extra.css";
		$pdfFileName = $pdfDIR . $post->post_name . ".pdf";
		$pdfFileURL  = get_site_url() . "/wp-content/uploads/blog_pdf/pdf/" . $post->post_name . ".pdf";
		$cssFileURL  = get_template_directory_uri() . "/style.css";
		$postContent = @do_shortcode( $post->post_content );
		$rxImagesAlt = "#(<img.*? alt\s?=(\s?['\"].*?['\"]).*?>)#";
		$outPut      = preg_replace_callback( $rxImagesAlt, function ( $result ) {
			$pixMarkup = $result[0];
			$altText   = trim( end( $result ), '"\'' );
			$markUp    = <<<MKP
<div class=='blog-pix-wrapper'>{$pixMarkup}</div>
<div style='blog-pix-alt-wrapper'>
	<p class='img-alt-text pz-img-alt-text-box'>
    <span class="pz-img-alt-text-text" >Alt. Text:</span>
    &nbsp;&nbsp;$altText
    </p>
</div>
MKP;

			return $markUp;
		}, $postContent );
		$outPut      = <<<OP
<!DOCTYPE html>
<html lang='de'>
	<head>
	    <meta charset='UTF-8'>
	    <title>{$post->post_title}</title>
	    <link rel='stylesheet' href='{$cssFileURL}' media='all' />
	    <link rel='stylesheet' href='{$css}' media='all' />
	</head>
	<body>
	    {$banner}
	    <div class='post full-post type-post status-publish format-standard has-post-thumbnail pz-main'>
	    {$outPut}
	    {$entryMeta}
	    {$authorProfile}
	    </div>
	</body	
</html>
OP;

		$htmlFileName               = $htmlDIR . $post->post_name . ".html";
		$assetsData['outPut']       = $outPut;
		$assetsData['pdfFileURL']   = $pdfFileURL;
		$assetsData['pdfFileName']  = $pdfFileName;
		$assetsData['htmlFileName'] = $htmlFileName;

		return $assetsData;
	}

	protected function grabBannerScrap( $permALink ) {
		$html      = file_get_contents( $permALink );
		$rxHeroBox = "#(<\!\-\- \.site-header \-\->.*?<\!\-\- \.hero-wrapper \-\->)#msi";
		preg_match( $rxHeroBox, $html, $matches );

		return ( isset( $matches[0] ) ) ? $matches[0] : "";
	}

	protected function grabEntryMetaScrap( $permALink ) {
		$html      = file_get_contents( $permALink );
		$rxHeroBox = "#(<\!\-\- \.entry-content \-\->.*?<\!\-\- \.entry-meta \-\->)#msi";
		preg_match( $rxHeroBox, $html, $matches );

		return ( isset( $matches[0] ) ) ? $matches[0] : "";
	}

	protected function grabAuthorProfileScrap( $permALink ) {
		$html      = file_get_contents( $permALink );
		$rxHeroBox = "#(<\!\-\- \#post\-\#\# \-\->.*?<\!\-\- \.author\-profile \-\->)#msi";
		preg_match( $rxHeroBox, $html, $matches );

		return ( isset( $matches[0] ) ) ? $matches[0] : "";
	}

	public function getCurrentPostType() {
		global $post, $typenow, $current_screen;
		//IF WE HAVE A POST, WE JUST EXTRACT THE POST TYPE FROM IT
		if ( $post && $post->post_type ) {
			return $post->post_type;
		} else if ( $typenow ) {
			//IF THE GLOBAL: $typenow (WHICH IS SET IN admin.php) IS HAS VALUE INSTEAD; RETURN IT
			return $typenow;
		} else if ( $current_screen && $current_screen->post_type ) {
			// OTHERWISE WE CHECK THE GLOBAL IF THE GLOBAL: $current_screen
			// (WHICH IS SET IN screen.php) HAS VALUE & RETURN ITS post_type PROPERTY INSTEAD
			return $current_screen->post_type;
		} else if ( isset( $_REQUEST['post_type'] ) ) {
			// FINALLY WE CHECK IF THE post_type KEY (QUERY-STRING) WAS SET IN THE $_REQUEST
			// AND THEN RETURN THE VALUE (SANITIZED).
			return sanitize_key( $_REQUEST['post_type'] );
		}

		# ALL FAILING, WE RETURN NULL
		return null;
	}

	public function addGeneratePDFButton() {
		global $post;
		$curPostType = $this->getCurrentPostType();
		$target      = "_blank";
		$permalink   = get_permalink( $post );
		$permalink   .= ( strstr( $permalink, "?" ) ) ? "&task=pdf" : "?task=pdf";
		$permalink   .= "&pid={$post->ID}";

		if ( strtolower( $curPostType ) === "post" ) {
			$btn = '<style type="text/css">';
			$btn .= '.pz-dld-icon.dashicons, .pz-dld-icon.dashicons-before:before{line-height:1.35;}';
			$btn .= '</style>';
			$btn .= '<a class="button button-default" target="' . $target . '" ';
			$btn .= ' href="' . $permalink . '">';
			$btn .= '<span class="dashicons dashicons-download pz-dld-icon"></span>';
			$btn .= 'Export this Post To PDF</a>';
			echo $btn;
		}
	}

	public function activateLB2PDFPlugin() {
		// TODO
	}

	public function deactivateLB2PDFPlugin() {
		// TODO
	}

}
