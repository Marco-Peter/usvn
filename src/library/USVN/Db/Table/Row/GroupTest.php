<?php
// Call USVN_Db_Table_Row_GroupTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "USVN_Db_Table_Row_GroupTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'library/USVN/autoload.php';


/**
 * Test class for USVN_Db_Table_Row_Group.
 * Generated by PHPUnit_Util_Skeleton on 2007-04-18 at 14:39:49.
 */
class USVN_Db_Table_Row_GroupTest extends USVN_Test_DB {
	/**
	 * @var USVN_Db_Table_Row_Group
	 */
	private $group1;
	/**
	 * @var USVN_Db_Table_Row_Group
	 */
	private $group2;
	private $users = array();

	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("USVN_Db_Table_Row_GroupTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp() {
		parent::setUp();
		$this->group1 = $this->createGroup("grp1");
		$this->group2 = $this->createGroup("grp2");

		$this->users["test"]  = $this->createUser("test", "test");
		$this->users["babar"] = $this->createUser("babar", "test");
		$this->users["john"]  = $this->createUser("john", "test");
    }

	public function testGroup()
	{
		$this->assertEquals('grp1', $this->group1->groups_name);
		$this->assertEquals('grp1', $this->group1->name);
		$this->assertEquals('grp2', $this->group2->groups_name);
		$this->assertEquals('grp2', $this->group2->name);
	}

	public function testAddUser()
	{
		$this->group1->addUser($this->users["test"]);
		$this->group1->addUser($this->users["babar"]);
		$res = array();
		$users = $this->group1->findManyToManyRowset('USVN_Db_Table_Users', 'USVN_Db_Table_UsersToGroups');
		foreach ($users as $user) {
			array_push($res, $user->login);
		}
		$this->assertContains("test", $res);
		$this->assertContains("babar", $res);
		$this->assertNotContains("john", $res);

		$this->group2->addUser($this->users["john"], true);
		$this->assertTrue($this->group2->userIsGroupLeader($this->users["john"]));
	}

	public function testDeleteUser()
	{
		$this->group1->addUser($this->users["test"]);
		$this->group2->addUser($this->users["test"]);
		$this->group1->addUser($this->users["john"]);
		$this->group2->addUser($this->users["john"]);
		$this->assertTrue($this->group1->hasUser($this->users["test"]));
		$this->assertTrue($this->group2->hasUser($this->users["test"]));

		$this->group2->deleteUser($this->users["test"]);
		$this->assertTrue($this->group1->hasUser($this->users["test"]));
		$this->assertFalse($this->group2->hasUser($this->users["test"]));

		$this->group1->deleteUser($this->users["test"]);
		$this->assertFalse($this->group1->hasUser($this->users["test"]));
		$this->assertFalse($this->group2->hasUser($this->users["test"]));

		$this->assertTrue($this->group1->hasUser($this->users["john"]));
		$this->assertTrue($this->group2->hasUser($this->users["john"]));

		$this->group2->deleteUser($this->users["john"]);
		$this->assertTrue($this->group1->hasUser($this->users["john"]));
		$this->assertFalse($this->group2->hasUser($this->users["john"]));

		$this->group1->deleteUser($this->users["john"]);
		$this->assertFalse($this->group1->hasUser($this->users["john"]));
		$this->assertFalse($this->group2->hasUser($this->users["john"]));
	}

	public function testDeleteAllUsers()
	{
		$this->group1->addUser($this->users["test"]);
		$this->group2->addUser($this->users["test"]);
		$this->group1->addUser($this->users["john"]);
		$this->group2->addUser($this->users["john"]);
		$this->assertTrue($this->group1->hasUser($this->users["test"]));
		$this->assertTrue($this->group2->hasUser($this->users["test"]));
		$this->assertTrue($this->group1->hasUser($this->users["john"]));
		$this->assertTrue($this->group2->hasUser($this->users["john"]));


		$this->group2->deleteAllUsers();
		$this->assertTrue($this->group1->hasUser($this->users["test"]));
		$this->assertTrue($this->group1->hasUser($this->users["john"]));
		$this->assertFalse($this->group2->hasUser($this->users["test"]));
		$this->assertFalse($this->group2->hasUser($this->users["john"]));

		$this->group1->deleteAllUsers();
		$this->assertFalse($this->group1->hasUser($this->users["test"]));
		$this->assertFalse($this->group1->hasUser($this->users["john"]));
		$this->assertFalse($this->group2->hasUser($this->users["test"]));
		$this->assertFalse($this->group2->hasUser($this->users["john"]));
	}

	public function testHasUser()
	{
		$user = $this->users["test"];
		$this->assertFalse($this->group1->hasUser($user));
		$this->group1->addUser($user);
		$this->assertTrue($this->group1->hasUser($user));
	}

	public function testUserIsGroupLeader()
	{
		$user2 = $this->users["test"];
		$user3 = $this->users["babar"];
		$user4 = $this->users["john"];
		$user_groups = new USVN_Db_Table_UsersToGroups();
		$user_groups->insert(
			array(
				"groups_id" => $this->group1->id,
				"users_id" => $user3->id,
				"is_leader" => 0
			)
		);
		$user_groups->insert(
			array(
				"groups_id" => $this->group1->id,
				"users_id" => $user4->id,
				"is_leader" => 1
			)
		);
		$this->assertFalse($this->group1->userIsGroupLeader($user2));
		$this->assertFalse($this->group1->userIsGroupLeader($user3));
		$this->assertTrue($this->group1->userIsGroupLeader($user4));
	}

	public function testPromoteUser()
	{
		$user = $this->users["test"];
		$this->group1->addUser($user);
		$this->assertFalse($this->group1->userIsGroupLeader($user));
		$this->group1->promoteUser($user);
		$this->assertTrue($this->group1->userIsGroupLeader($user));
	}

	public function testPromoteUserNotGroupMember()
	{
		$user = $this->users["test"];
		try {
			$this->group1->promoteUser($user);
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testGetGroupLeaders()
	{
		$user2 = $this->users["test"];
		$user3 = $this->users["babar"];

		$this->assertEquals(0, count($this->group1->getGroupLeaders()));

		$this->group1->addUser($user2);
		$this->group1->addUser($user3);
		$this->assertEquals(0, count($this->group1->getGroupLeaders()));

		$this->group1->promoteUser($user2);
		$leaders = $this->group1->getGroupLeaders();
		$this->assertEquals(1, count($leaders));

		$this->assertEquals($user2->id,    $leaders->current()->id);
		$this->assertEquals($user2->login, $leaders->current()->login);

		$this->group1->promoteUser($user3);
		$this->assertEquals(2, count($this->group1->getGroupLeaders()));
	}

	public function testHasUserNewGroup()
	{
		$groups = new USVN_Db_Table_Groups();
		$group = $groups->createRow();
		$group->name = 'test';
		$group->save();
		$user2 = $this->users["test"];
		$this->assertFalse($group->hasUser($user2));
	}
}

// Call USVN_Db_Table_Row_GroupTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "USVN_Db_Table_Row_GroupTest::main") {
    USVN_Db_Table_Row_GroupTest::main();
}
?>
