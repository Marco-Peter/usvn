<?php
/**
 * Display project homepage.
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.5
 * @package usvn
 * @subpackage project
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

class ProjectController extends USVN_Controller
{
	/**
	 * Project row object
	 *
	 * @var USVN_Db_Table_Row_Project
	 */
	protected $_project;

	/**
     * Pre-dispatch routines
     *
     * Called before action method. If using class with
     * {@link Zend_Controller_Front}, it may modify the
     * {@link $_request Request object} and reset its dispatched flag in order
     * to skip processing the current action.
     *
     * @return void
     */
	public function preDispatch()
	{
		parent::preDispatch();

		$project = str_replace(USVN_URL_SEP, '/', $this->getRequest()->getParam('project'));
		$table = new USVN_Db_Table_Projects();
		$project = $table->fetchRow(array("projects_name = ?" => $project));
		/* @var $project USVN_Db_Table_Row_Project */
		if ($project === null) {
			$this->_redirect("/");
		}
		$this->_project = $project;

		$this->view->isAdmin = $this->isAdmin();

		$user = $this->getRequest()->getParam('user');
		$this->view->user = $user;
		$this->view->secret_id = $user->secret_id;
		/* @var $user USVN_Db_Table_Row_User */
		$groups = $user->findManyToManyRowset("USVN_Db_Table_Groups", "USVN_Db_Table_UsersToGroups");
		$find = false;
		foreach ($groups as $group) {
			if ($project->groupIsMember($group)) {
				$find = true;
				break;
			}
		}
		if (!$find && !$this->isAdmin()) {
			$this->_redirect("/");
		}
    $this->view->submenu = array(
        array('label' => $project->name),
        array('label' => 'Index',    'url' => array('action' => '', 'project' => $project->name), 'route' => 'project'),
        array('label' => 'Timeline', 'url' => array('action' => 'timeline', 'project' => $project->name), 'route' => 'project'),
        array('label' => 'Browser',  'url' => array('action' => 'browser', 'project' => $project->name), 'route' => 'project')
    );
	}

	protected function isAdmin()
	{
		if (!isset($this->view->isAdmin)) {
			$user = $this->getRequest()->getParam('user');
			$this->view->isAdmin = $this->_project->userIsAdmin($user) || $user->is_admin;
		}
		return $this->view->isAdmin;
	}

	protected function requireAdmin()
	{
		if (!$this->isAdmin()) {
			$this->_redirect("/project/{$this->_project->name}/");
		}
	}

	public function indexAction()
	{
		$this->view->project = $this->_project;
		$SVN = new USVN_SVN($this->_project->name);
		$config = Zend_Registry::get('config');
		$this->view->subversion_url = $config->subversion->url . $this->_project->name;
		foreach ($SVN->listFile('/') as $dir) {
			if ($dir['name'] == 'trunk')
				$this->view->subversion_url .= '/trunk';
		}
		$this->view->log = $SVN->log(5);
	}


	public function browserAction()
	{
		$this->view->project = $this->_project;
	}

	public function timelineAction()
	{
//		$project = $this->getRequest()->getParam('project');
//		$table = new USVN_Db_Table_Projects();
//		$project = $table->fetchRow(array("projects_name = ?" => $project));
//		/* @var $project USVN_Db_Table_Row_Project */
//		if ($project === null) {
//			$this->_redirect("/");
//		}
//		$this->_project = $project;
		$project = $this->_project;

		//get the identity of the user
		$identity = Zend_Auth::getInstance()->getIdentity();

		$user_table = new USVN_Db_Table_Users();
		$user = $user_table->fetchRow(array('users_login = ?' => $identity['username']));

//		$table = new USVN_Db_Table_Projects();
//		$this->view->project = $table->fetchRow(array('projects_name = ?' => $project->name));

		$table = new USVN_Db_Table_UsersToProjects();
		$userToProject = $table->fetchRow(array(
			'users_id = ?' => $user->users_id,
			'projects_id = ?' => $project->projects_id));

		if ($userToProject == null)
		{
		//search project
//			$this->view->project = $project;
//			$this->view->user = $user;
//			$this->render('accesdenied');
//			return;
		}

		$this->view->project = $project;
		$SVN = new USVN_SVN($this->_project->name);
		$this->view->log = $SVN->log(100);
	}

	public function adduserAction()
	{
		$this->requireAdmin();
		$table = new USVN_Db_Table_Users();
		$user = $table->fetchRow(array("users_login = ?" => $this->getRequest()->getParam('users_login')));
		if ($user !== null) {
			try {
				$this->_project->addUser($user);
			}
			catch (Exception $e) {
			}
		}
		$this->_redirect("/project/{$this->_project->name}/");
	}

	public function deleteuserAction()
	{
		$this->requireAdmin();
		$this->_project->deleteUser($this->getRequest()->getParam('users_id'));
		$this->_redirect("/project/{$this->_project->name}/");
	}

	public function addgroupAction()
	{
		$this->requireAdmin();
		$table = new USVN_Db_Table_Groups();
		$group = $table->fetchRow(array("groups_name = ?" => $this->getRequest()->getParam('groups_name')));
		if ($group !== null) {
			try {
				$this->_project->addGroup($group);
			}
			catch (Exception $e) {
			}
		}
		$this->_redirect("/project/{$this->_project->name}/");
	}

	public function deletegroupAction()
	{
		$this->requireAdmin();
		$this->_helper->viewRenderer->setNoRender();
		$this->_project->deleteGroup($this->getRequest()->getParam('groups_id'));
		$this->_redirect("/project/{$this->_project->name}/");
	}
	
	/**
   * Display a file using appropriate highlighting
   *
   * @return void
   * @author Zak
   */
	public function showAction()
	{
	  include_once('geshi/geshi.php');
	  $this->view->project = $this->_project;
    $config = new USVN_Config_Ini(USVN_CONFIG_FILE, USVN_CONFIG_SECTION);
	  $project_name = str_replace(USVN_URL_SEP, '/',$this->_project->name);
    $svn_file_path = $this->getRequest()->getParam('file');
    $this->view->path = $svn_file_path;
    $local_file_path = USVN_SVNUtils::getRepositoryPath($config->subversion->path."/svn/".$project_name."/".$svn_file_path);
    $file_ext = pathinfo($svn_file_path, PATHINFO_EXTENSION);
    $cmd = USVN_SVNUtils::svnCommand("cat --non-interactive $local_file_path");
    $source = USVN_ConsoleUtils::runCmdCaptureMessageUnsafe($cmd, $return);
    if ($return) {
      throw new USVN_Exception(T_("Can't read from subversion repository.\nCommand:\n%s\n\nError:\n%s"), $cmd, $message);
    } else {
      $geshi = new Geshi();
      $lang_name = $geshi->get_language_name_from_extension($file_ext);
      $this->view->language = $lang_name;
      $geshi->set_language($lang_name, true);
      $geshi->set_source($source);
      $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
      $geshi->set_header_type(GESHI_HEADER_DIV);
      $this->view->highlighted_source = $geshi->parse_code();
    }
	}
}