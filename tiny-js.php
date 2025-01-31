<?php 
/*

	Tinifier
	---------

	@file 		tiny.php
	@date 		2011-05-27 22:20:36 -0400 (Wed, 1 June 2011)
	@author 	Jack Lightbody <jack.lightbody@gmail.com>
	Copyright   (c) 2011 Jack Lightbody <12345j.co.cc>
	@license 	Mit Open Source
	@github     https://github.com/12345j/Tinifier-Concrete5-Optimiser
	@version    1.3.7
*/
defined( 'C5_EXECUTE' ) or die( "Access Denied." );

class TinyHelper {
		public function tinify( $content ){
			$file=loader::helper('file');
			$jsFileMerge = DIRNAME_JAVASCRIPT."/merge.js";
			$cssFileMerge = DIRNAME_CSS."/merge.css";
			if(file_exists($cssFileMerge)){
				unlink($cssFileMerge);
			}
			if(file_exists($jsFileMerge)){
				unlink($jsFileMerge);
			}
			$jsCombine=array();
			$cssCombine=array();
			$unknownCss=array();
			$unknownJs=array();
			// Get all the javascript links to files and put their content in the merge js file			
			if ( preg_match_all( '#<\s*script\s*(type="text/javascript"\s*)?src=.+<\s*/script\s*>#smUi',$content,$jsLinks )) {
				foreach ( $jsLinks[0] as $jsLink ) {
					$content=str_replace($jsLink, '', $content);
					$content=str_replace('</body>', $jsLink.'</body>', $content);
				}	
			}
			// get all the inline javascript and add it to the footer (we need this below the merge)
			if(preg_match_all( '#<\s*script\s*(type="text/javascript"\s*)?>(.+)<\s*/script\s*>#smUi',$content,$inlineJavascript )){
				foreach ($inlineJavascript[0] as $inlineItem ) {
					$content=str_replace($inlineItem, '', $content);
					$content=str_replace('</body>', $inlineItem.'</body>', $content);
				}	
			}
			// get all the css links and add to merge
			if ( preg_match_all( '#<\s*link\s*rel="?stylesheet"?.+>#smUi',$content,$cssLinks )) {
				foreach ($cssLinks[0] as $cssLink ) {
					if(preg_match('/<link rel="stylesheet" type="text\/css" href="(.*)" \/>/', $cssLink )){
         					$cssItem= preg_replace('/<link rel="stylesheet" type="text\/css" href="(.*)" \/>/', '$1', $cssLink);// get whats in href attr  
         					array_push($cssCombine, $cssItem);
         				}else{
         					array_push($unknownCss, $cssLink);
         				}
         				$content=str_replace($cssLink, '', $content);
				}	
				foreach($cssCombine as $css){				
					$cssFile=BASE_URL.$css;
			 		$cssFileContents=$file->getContents($cssFile);
			 		//$cssFileContent=preg_replace("#\url((.*)\)#is", '('.$css.'$1'.')', $cssFileContents);
			 		$cssCompress=cssCompress($cssFileContents);
					file_put_contents($cssFileMerge, $cssCompress, FILE_APPEND);	
				}
			}
				// get all the inline css and add to merge
				if ( preg_match( '#<\s*style.*>.+<\s*/style\s*\/?>#smUi',$content,$inlineCss )>0 ) {
					foreach ( $inlineCss as $Inlinecssitem ) {
						$Inlinecssitem1=preg_replace('#<\s*style.*>#smUi', "", $Inlinecssitem);
						$Inlinecssitem1=preg_replace('#<\s*/style\s*\/?>#smUi', "", $Inlinecssitem1);
						$cssCompress=cssCompress($Inlinecssitem1);
						file_put_contents($cssFileMerge, $cssCompress, FILE_APPEND);
						$content=str_replace($Inlinecssitem, '', $content);
					}	
				}
				foreach($unknownJs as $jsU){
					$content=str_ireplace('</body>', $jsU.'</body>', $content);	// add the js link to the end					
				}
				foreach($unknownCss as $cssU){
					$content=str_ireplace( '</head>',$cssU.'</head>', $content );	// add the stylesheet link to the head					
				}
				$content =  str_ireplace( '</head>','<link rel="stylesheet" type="text/css" href="'.ASSETS_URL_WEB.'/css/merge.css" /><!--Compressed by Tinifier v1.3.7--></head>', $content );	// add the stylesheet link to the head
				$content = preg_replace('/(?:(?<=\>)|(?<=\/\)))(\s+)(?=\<\/?)/','',$content);//remove html whitespace
				return $content;	
		}}
		function cssCompress($string) {
			/* remove comments */
		    $string = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $string);
			/* remove tabs, spaces, new lines, etc. */        
		    $string = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $string);
			/* remove unnecessary spaces */        
		    $string = str_replace('{ ', '{', $string);
		    $string = str_replace(' }', '}', $string);
		    $string = str_replace('; ', ';', $string);
		    $string = str_replace(', ', ',', $string);
		    $string = str_replace(' {', '{', $string);
		    $string = str_replace('} ', '}', $string);
		    $string = str_replace(': ', ':', $string);
		    $string = str_replace(' ,', ',', $string);
		    $string = str_replace(' ;', ';', $string); 
			return $string;
		}