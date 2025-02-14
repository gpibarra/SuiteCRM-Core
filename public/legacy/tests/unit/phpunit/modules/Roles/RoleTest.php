<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class RoleTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testRole()
    {
        // Execute the constructor and check for the Object type and  attributes
        $role = BeanFactory::newBean('Roles');

        $this->assertInstanceOf('Role', $role);
        $this->assertInstanceOf('SugarBean', $role);

        $this->assertAttributeEquals('roles', 'table_name', $role);
        $this->assertAttributeEquals('roles_modules', 'rel_module_table', $role);
        $this->assertAttributeEquals('Roles', 'module_dir', $role);
        $this->assertAttributeEquals('Role', 'object_name', $role);

        $this->assertAttributeEquals(true, 'new_schema', $role);
        $this->assertAttributeEquals(true, 'disable_row_level_security', $role);
    }

    public function testget_summary_text()
    {
        $role = BeanFactory::newBean('Roles');

        //test without setting name
        $this->assertEquals(null, $role->get_summary_text());

        //test with name set
        $role->name = 'test';
        $this->assertEquals('test', $role->get_summary_text());
    }

    public function testcreate_export_query()
    {
        $role = BeanFactory::newBean('Roles');

        //test with empty string params
        $expected = ' SELECT  roles.*  FROM roles  where roles.deleted=0';
        $actual = $role->create_export_query('', '');
        $this->assertSame($expected, $actual);

        //test with valid string params
        $expected = ' SELECT  roles.*  FROM roles  where (roles.name = "") AND roles.deleted=0';
        $actual = $role->create_export_query('roles.id', 'roles.name = ""');
        $this->assertSame($expected, $actual);
    }

    public function testSet_module_relationshipAndQuery_modules()
    {
        $role = BeanFactory::newBean('Roles');

        $role->id = 1;
        $mod_ids = array('Accounts', 'Leads');

        //test set_module_relationship.
        //creates related records
        $role->set_module_relationship($role->id, $mod_ids, 1);

        //get the related records count
        $result = $role->query_modules();
        $this->assertGreaterThanOrEqual(2, count((array)$result));

        //test clear_module_relationship method
        $this->clear_module_relationship($role->id);
    }

    public function clear_module_relationship($id)
    {
        $role = BeanFactory::newBean('Roles');

        $role->id = $id;
        $role->clear_module_relationship($id);

        //get related records count and verify that records are removed
        $result = $role->query_modules();
        $this->assertEquals(0, count((array)$result));
    }

    public function testSet_user_relationshipAndCheck_user_role_count()
    {
        // test
        $role = BeanFactory::newBean('Roles');

        $role->id = 1;
        $user_ids = array('1', '2');

        //create related records
        $role->set_user_relationship($role->id, $user_ids, 1);

        //get the related records count
        $result = $role->check_user_role_count('1');
        $this->assertGreaterThanOrEqual(1, count((array)$result));

        //get the related records count
        $result = $role->check_user_role_count('2');
        $this->assertGreaterThanOrEqual(1, count((array)$result));

        //test get_users method
        $this->get_users($role->id);

        //test clear_user_relationship method
        $this->clear_user_relationship($role->id, '1');
        $this->clear_user_relationship($role->id, '2');
    }

    public function get_users($id)
    {
        $role = BeanFactory::newBean('Roles');

        $role->id = $id;
        $result = $role->get_users();

        $this->assertTrue(is_array($result));
    }

    public function clear_user_relationship($role_id, $user_id)
    {
        $role = BeanFactory::newBean('Roles');

        //get related records count and verify that records are removed
        $result = $role->clear_user_relationship($role_id, $user_id);
        $this->assertEquals(0, count((array)$result));
    }

    public function testquery_user_allowed_modules()
    {
        $role = BeanFactory::newBean('Roles');

        $result = $role->query_user_allowed_modules('1');
        $this->assertTrue(is_array($result));
    }

    public function testquery_user_disallowed_modules()
    {
        $role = BeanFactory::newBean('Roles');

        $allowed = array('Accounts' => 'Accounts', 'Leads' => 'Leads');
        $result = $role->query_user_disallowed_modules(null, $allowed);

        $this->assertTrue(is_array($result));
    }
}
