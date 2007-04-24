<?php
/**
 * Base class for modules menu
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.5
 * @package usvn
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */
abstract class USVN_modules_default_AbstractMenu
{
	/**
	* Get menu entries in top menu.
	*
	* @param Zend_Controller_Request_Abstract Request
	* @param mixed|null Identity from Zend_Auth
	* @return array
	*/
	public static function getTopMenu($request, $identity)
	{
		return array();
	}

	/**
	* Get menu entries in sub menu.
	*
	* @param Zend_Controller_Request_Abstract Request
	* @param mixed|null Identity from Zend_Auth
	* @return array
	*/
	public static function getSubMenu($request, $identity)
	{
		return array();
	}
}