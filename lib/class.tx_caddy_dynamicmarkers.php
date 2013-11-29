<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 - Dirk Wildt <http://wildt.at.die-netzmacher.de>
 *  All rights reserved
 *
 *  Caddy is a fork of wt_cart (version 1.4.6)
 *  (c) wt_cart 2010-2012 - wt_cart Development Team <info@wt-cart.com>
 * 
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_tslib . 'class.tslib_pibase.php');

/**
 * Plugin 'Cart' for the 'caddy' extension.
 *
 * @author	Dirk Wildt <http://wildt.at.die-netzmacher.de>
 * @package    TYPO3
 * @subpackage    tx_caddy
 * @version     3.0.1
 * @since       1.4.6
 */

class tx_caddy_dynamicmarkers extends tslib_pibase {

  public $extKey = 'caddy';
  public $scriptRelPath = 'pi1/class.tx_caddy_pi1.php';
  // Path to pi1 to get locallang.xml from pi1 folder
  public $locallangmarker_prefix = array(// prefix for automatic locallangmarker
//          'CADDY_LL_', // prefix for HTML template part
//          'caddy_ll_' // prefix for typoscript part
          '_LOCAL_LANG_', // prefix for HTML template part
          null // prefix for typoscript part
  );
  public $typoscriptmarker_prefix = array(// prefix for automatic typoscriptmarker
          '_HTMLMARKER_', // prefix for HTML template part
          '_HTMLMARKER' // prefix for typoscript part
  );

 /**
  * main( ) : replace typoscript- and locallang markers
  *
  * @param      string    $content  : current content with HTML marker
  * @param      object    $pObj     : parent object
  * @return	string    $content  : current content with replaced HTML marker
  * @access public
  * @version    3.0.1
  * @since      1.4.0
  */
  function main( $content, $pObj ) 
  {
      // config
    $this->conf     = $pObj->conf;
    $this->cObj     = $pObj->cObj;
    $this->content  = $content;
    
      // #i0037, dwildt, 1+
    $this->content  = $this->cObjData( );
    
    $this->pi_loadLL();

      // 1. replace locallang markers
      // Automaticly fill locallangmarkers with fitting value of locallang.xml
    $this->content  = preg_replace_callback
                      (
                        '#\#\#\#' . $this->locallangmarker_prefix[0] . '(.*)\#\#\##Uis', // regulare expression
                        array
                        (
                          $this, 
                          'DynamicLocalLangMarker'
                        ), // open function
                        $this->content // current content
                      );

      // 2. replace typoscript markers
      // Automaticly fill locallangmarkers with fitting value of locallang.xml
    $this->content  = preg_replace_callback
                      (
                        '#\#\#\#' . $this->typoscriptmarker_prefix[0] . '(.*)\#\#\##Uis', // regulare expression
                        array
                        (
                          $this, 
                          'DynamicTyposcriptMarker'
                        ), // open function
                        $this->content // current content
                      );

      // #i0036, dwildt, 5+
    if( $this->conf['debug.']['dontReplaceEmptyMarker'] )
    {
      return $this->content;
    }
    $this->content = preg_replace( '|###.*?###|i', '', $this->content );
    
  }

 /**
  * DynamicLocalLangMarker( ) : get automaticly a marker from locallang.xml (###LOCALLANG_BLABLA###)
  *
  * @param      array     $array
  * @return	string    $content  : current content with replaced HTML marker
  * @access public
  * @version    3.0.1
  * @since      1.4.0
  */
  private function DynamicLocalLangMarker( $array ) 
  {
    if( ! empty( $array[1] ) )
    {
      $string = $this->pi_getLL
                ( 
                  strtolower( $this->locallangmarker_prefix[1] . $array[1] ), 
                  '<i>' . strtolower( $array[1] ) . '</i>'
                ); 
    }

    if( ! empty( $string ) )
    {
      return $string;
    }
  }

 /**
  * DynamicTyposcriptMarker( )  : Get automaticly a marker from typoscript
  *
  * @param      array     $array
  * @return	string    $content  : current content with replaced HTML marker
  * @access public
  * @version    3.0.1
  * @since      1.4.0
  */
  private function DynamicTyposcriptMarker( $array ) 
  {
    if( $this->conf[$this->typoscriptmarker_prefix[1] . '.'][strtolower( $array[1] )] )
    { // If there is a fitting entry in typoscript
      $string = $this->cObj->cObjGetSingle
                (
                  $this->conf[$this->typoscriptmarker_prefix[1] . '.'][strtolower( $array[1] )], 
                  $this->conf[$this->typoscriptmarker_prefix[1] . '.'][strtolower( $array[1]) . '.'] 
                ); // fill string with typoscript value
    }

    if( ! empty( $string ) )
    {
      return $string;
    }
  }

 /**
  * cObjData( ) : Replace all marker, which correspond with cObjData like ###UID###, ###PID###
  *
  * @param      string    $content  : current content with HTML marker
  * @return	string    $content  : current content with replaced HTML marker
  * @access     private
  * @internal   #i0037
  * @version    3.0.1
  * @since      1.4.0
  */
  private function cObjData( ) 
  {
    foreach( $this->cObj->data as $key => $value )
    {
      if( empty( $value ) )
      {
        continue;
      }
      $hashMarker     = '###'.strtoupper($key).'###';
      $this->content  = str_replace( $hashMarker, $value, $this->content );
    }
    return $this->content;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caddy/lib/class.tx_caddy_dynamicmarkers.php']) 
{
  include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/caddy/lib/class.tx_caddy_dynamicmarkers.php']);
}
?>