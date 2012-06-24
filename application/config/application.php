<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * External executables 
 */
$config['bin']['convert']       = 'convert';
$config['bin']['gs']            = 'gs';
$config['bin']['identify']      = 'identify';
$config['bin']['ocr']           = 'tesseract';
$config['bin']['pdftk']         = 'pdftk';
$config['bin']['pdffonts']      = 'pdffonts';
$config['bin']['grep']          = 'grep';

/**
 * OCR config 
 */
//	$config['ocr']['sdevice']          = 'tiffg4';
//	$config['ocr']['extension']        = 'tif';

	$config['ocr']['sdevice']       = 'tiffgray';
	$config['ocr']['extension']     = 'tif';
	$config['ocr']['resolution']    = '288';
    
//	$config['ocr']['density']  = '288x288';
//	$config['ocr']['quality']  = '95';
//	$config['ocr']['geometry'] = '1034';