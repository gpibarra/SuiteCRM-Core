<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class SecurityGroupTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }
    public function testSecurityGroup()
    {
        // Execute the constructor and check for the Object type and  attributes
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $this->assertInstanceOf('SecurityGroup', $securityGroup);
        $this->assertInstanceOf('Basic', $securityGroup);
        $this->assertInstanceOf('SugarBean', $securityGroup);

        $this->assertAttributeEquals('securitygroups', 'table_name', $securityGroup);
        $this->assertAttributeEquals('SecurityGroups', 'module_dir', $securityGroup);
        $this->assertAttributeEquals('SecurityGroup', 'object_name', $securityGroup);
    }

    public function testgetGroupWhere()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        //test with securitygroups module
        $expected = " securitygroups.id in (
                select secg.id from securitygroups secg
                inner join securitygroups_users secu on secg.id = secu.securitygroup_id and secu.deleted = 0
                    and secu.user_id = '1'
                where secg.deleted = 0
            )";
        $actual = $securityGroup->getGroupWhere('securitygroups', 'SecurityGroups', 1);
        $this->assertSame($expected, $actual);

        //test with //test with securitygroups module module
        $table_name = 'users';
        $module = 'Users';
        $user_id = 1;
        $expected = " EXISTS (SELECT  1
                  FROM    securitygroups secg
                          INNER JOIN securitygroups_users secu
                            ON secg.id = secu.securitygroup_id
                               AND secu.deleted = 0
                               AND secu.user_id = '$user_id'
                          INNER JOIN securitygroups_records secr
                            ON secg.id = secr.securitygroup_id
                               AND secr.deleted = 0
                               AND secr.module = '$module'
                       WHERE   secr.record_id = ".$table_name.".id
                               AND secg.deleted = 0) ";
        $actual = $securityGroup->getGroupWhere($table_name, $module, $user_id);
        $this->assertSame($expected, $actual);
    }

    public function testgetGroupUsersWhere()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $expected = " users.id in (
            select sec.user_id from securitygroups_users sec
            inner join securitygroups_users secu on sec.securitygroup_id = secu.securitygroup_id and secu.deleted = 0
                and secu.user_id = '1'
            where sec.deleted = 0
        )";
        $actual = $securityGroup::getGroupUsersWhere(1);

        $this->assertSame($expected, $actual);
    }

    public function testgetGroupJoin()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        //test with securitygroups module
        $expected = " LEFT JOIN (select distinct secg.id from securitygroups secg
    inner join securitygroups_users secu on secg.id = secu.securitygroup_id and secu.deleted = 0
            and secu.user_id = '1'
    where secg.deleted = 0
) securitygroup_join on securitygroup_join.id = securitygroups.id ";
        $actual = $securityGroup->getGroupJoin('securitygroups', 'SecurityGroups', 1);
        $this->assertSame($expected, $actual);

        //test with //test with securitygroups module
        $expected = " LEFT JOIN (select distinct secr.record_id as id from securitygroups secg
    inner join securitygroups_users secu on secg.id = secu.securitygroup_id and secu.deleted = 0
            and secu.user_id = '1'
    inner join securitygroups_records secr on secg.id = secr.securitygroup_id and secr.deleted = 0
             and secr.module = 'Users'
    where secg.deleted = 0
) securitygroup_join on securitygroup_join.id = users.id ";
        $actual = $securityGroup->getGroupJoin('users', 'Users', 1);
        $this->assertSame($expected, $actual);
    }

    public function testgetGroupUsersJoin()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $expected = " LEFT JOIN (
            select distinct sec.user_id as id from securitygroups_users sec
            inner join securitygroups_users secu on sec.securitygroup_id = secu.securitygroup_id and secu.deleted = 0
                and secu.user_id = '1'
            where sec.deleted = 0
        ) securitygroup_join on securitygroup_join.id = users.id ";
        $actual = $securityGroup->getGroupUsersJoin(1);
        $this->assertSame($expected, $actual);
    }

    public function testgroupHasAccess()
    {
        //test for listview
        $result = SecurityGroup::groupHasAccess('', '[SELECT_ID_LIST]');
        $this->assertEquals(true, $result);

        //test with invalid values
        $result = SecurityGroup::groupHasAccess('', '');
        $this->assertEquals(false, $result);

        //test with valid values
        $result = SecurityGroup::groupHasAccess('Users', '1');
        $this->assertEquals(false, $result);
    }

    public function testinherit()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->id = 1;

        $_REQUEST['subpanel_field_name'] = 'id';

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            SecurityGroup::inherit($account, false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testassign_default_groups()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->id = 1;

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            SecurityGroup::assign_default_groups($account, false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testinherit_creator()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->id = 1;

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            SecurityGroup::inherit_creator($account, false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testinherit_assigned()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->id = 1;
        $account->assigned_user_id = 1;

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            SecurityGroup::inherit_assigned($account, false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testinherit_parent()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->id = 1;

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            SecurityGroup::inherit_parent($account, false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testinherit_parentQuery()
    {
        $account = BeanFactory::newBean('Accounts');
        $account->id = 1;

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            SecurityGroup::inherit_parentQuery($account, 'Accounts', 1, 1, $account->module_dir);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testinheritOne()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->inheritOne(1, 1, 'Accounts');
        $this->assertEquals(false, $result);
    }

    public function testgetMembershipCount()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->getMembershipCount('1');
        $this->assertEquals(0, $result);
    }

    public function testSaveAndRetrieveAndRemoveDefaultGroups()
    {
        // unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        $db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        //create a security group first
        $securityGroup->name = 'test';
        $securityGroup->save();

        //execute saveDefaultGroup method
        $securityGroup->saveDefaultGroup($securityGroup->id, 'test_module');

        //execute retrieveDefaultGroups method
        $result = $securityGroup->retrieveDefaultGroups();

        //verify that default group is created
        $this->assertTrue(is_array($result));
        $this->assertGreaterThan(0, count($result));

        //execute removeDefaultGroup method for each default group
        foreach ($result as $key => $value) {
            $securityGroup->removeDefaultGroup($key);
        }

        //retrieve back and verify that default securith groups are deleted
        $result = $securityGroup->retrieveDefaultGroups();
        $this->assertEquals(0, count($result));

        //delete the security group as well for cleanup
        $securityGroup->mark_deleted($securityGroup->id);
    }

    public function testgetSecurityModules()
    {
        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $expected = array(
            'Meetings',
            'Cases',
            'AOS_Products',
            'Opportunities',
            'FP_Event_Locations',
            'Tasks',
            'jjwg_Markers',
            'EmailMarketing',
            'EmailTemplates',
            'Campaigns',
            'jjwg_Areas',
            'Contacts',
            'AOS_Contracts',
            'AOS_Quotes',
            'Bugs',
            'Users',
            'Documents',
            'AOS_Invoices',
            'Notes',
            'AOW_WorkFlow',
            'ProspectLists',
            'AOK_KnowledgeBase',
            'AOS_PDF_Templates',
            'Calls',
            'Accounts',
            'Leads',
            'Emails',
            'ProjectTask',
            'Project',
            'FP_events',
            'AOR_Reports',
            'AOR_Scheduled_Reports',
            'Prospects',
            'ACLRoles',
            'jjwg_Maps',
            'AOS_Product_Categories',
            'Spots' => 'Spots',
            'SurveyQuestionOptions' => 'SurveyQuestionOptions',
            'SurveyQuestionResponses' => 'SurveyQuestionResponses',
            'SurveyQuestions' => 'SurveyQuestions',
            'SurveyResponses' => 'SurveyResponses',
            'Surveys' => 'Surveys',
        );

        $actual = $securityGroup->getSecurityModules();
        $actualKeys = array_keys($actual);
        sort($expected);
        sort($actualKeys);
        $this->assertSame($expected, $actualKeys);
    }

    public function testgetLinkName()
    {
        //unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        $db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->getLinkName('Accounts', 'Contacts');
        $this->assertEquals('contacts', $result);

        $result = $securityGroup->getLinkName('SecurityGroups', 'ACLRoles');
        $this->assertEquals('aclroles', $result);
    }

    public function testaddGroupToRecord()
    {
        // unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        //$db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $securityGroup->addGroupToRecord('Accounts', 1, 1);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testremoveGroupFromRecord()
    {
        //unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        //$db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $securityGroup->removeGroupFromRecord('Accounts', 1, 1);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testgetUserSecurityGroups()
    {
        //unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        //$db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->getUserSecurityGroups('1');

        $this->assertTrue(is_array($result));
    }

    public function testgetAllSecurityGroups()
    {
        //unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        //$db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->getAllSecurityGroups();

        $this->assertTrue(is_array($result));
    }

    public function testgetMembers()
    {
        //unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        //$db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->getMembers();

        $this->assertTrue(is_array($result));
    }

    public function testgetPrimaryGroupID()
    {
        //unset and reconnect Db to resolve mysqli fetch exeception
        $db = DBManagerFactory::getInstance();
        //$db->disconnect();
        unset($db->database);
        $db->checkConnection();

        $securityGroup = BeanFactory::newBean('SecurityGroups');

        $result = $securityGroup->getPrimaryGroupID();

        $this->assertEquals(null, $result);
    }
}
