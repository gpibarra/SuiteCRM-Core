<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class MeetingTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testMeeting()
    {
        // Execute the constructor and check for the Object type and  attributes
        $meeting = BeanFactory::newBean('Meetings');

        $this->assertInstanceOf('Meeting', $meeting);
        $this->assertInstanceOf('SugarBean', $meeting);

        $this->assertAttributeEquals('Meetings', 'module_dir', $meeting);
        $this->assertAttributeEquals('Meeting', 'object_name', $meeting);
        $this->assertAttributeEquals('meetings', 'table_name', $meeting);

        $this->assertAttributeEquals(true, 'new_schema', $meeting);
        $this->assertAttributeEquals(true, 'importable', $meeting);
        $this->assertAttributeEquals(false, 'syncing', $meeting);
        $this->assertAttributeEquals(true, 'update_vcal', $meeting);

        $this->assertAttributeEquals('meetings_users', 'rel_users_table', $meeting);
        $this->assertAttributeEquals('meetings_contacts', 'rel_contacts_table', $meeting);
        $this->assertAttributeEquals('meetings_leads', 'rel_leads_table', $meeting);

        $this->assertAttributeEquals(null, 'cached_get_users', $meeting);
        $this->assertAttributeEquals(false, 'date_changed', $meeting);
    }

    public function testACLAccess()
    {
        $meeting = BeanFactory::newBean('Meetings');

        //test without recurring_source
        $this->assertEquals(true, $meeting->ACLAccess('edit'));
        $this->assertEquals(true, $meeting->ACLAccess('save'));
        $this->assertEquals(true, $meeting->ACLAccess('editview'));
        $this->assertEquals(true, $meeting->ACLAccess('delete'));

        //test with recurring_source
        $meeting->recurring_source = 'test';
        $this->assertEquals(false, $meeting->ACLAccess('edit'));
        $this->assertEquals(false, $meeting->ACLAccess('save'));
        $this->assertEquals(false, $meeting->ACLAccess('editview'));
        $this->assertEquals(false, $meeting->ACLAccess('delete'));
    }

    public function testhasIntegratedMeeting()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $result = $meeting->hasIntegratedMeeting();
        $this->assertEquals(false, $result);
    }

    public function testSaveAndMarkdeletedAndSetAcceptStatus()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $meeting->name = 'test';
        $meeting->status = 'Not Held';
        $meeting->type = 'Sugar';
        $meeting->description = 'test description';
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = 1;
        $meeting->date_start = '2016-02-11 17:30:00';
        $meeting->date_end = '2016-02-11 17:30:00';

        $meeting->save();

        //test for record ID to verify that record is saved
        $this->assertTrue(isset($meeting->id));
        $this->assertEquals(36, strlen($meeting->id));

        /* Test set_accept_status method */

        //test set_accept_status with User object
        $user = BeanFactory::newBean('Users');
        $meeting->set_accept_status($user, 'accept');

        //test set_accept_status with contact object
        $contact = BeanFactory::newBean('Contacts');
        $meeting->set_accept_status($contact, 'accept');

        //test set_accept_status with Lead object
        $lead = BeanFactory::newBean('Leads');
        $meeting->set_accept_status($lead, 'accept');

        //mark all created relationships as deleted
        $meeting->mark_relationships_deleted($meeting->id);

        //mark the record as deleted and verify that this record cannot be retrieved anymore.
        $meeting->mark_deleted($meeting->id);
        $result = $meeting->retrieve($meeting->id);
        $this->assertEquals(null, $result);
    }

    public function testget_summary_text()
    {
        $meeting = BeanFactory::newBean('Meetings');

        //test without setting name
        $this->assertEquals(null, $meeting->get_summary_text());

        //test with name set
        $meeting->name = 'test';
        $this->assertEquals('test', $meeting->get_summary_text());
    }

    public function testcreate_export_query()
    {
//        $this->markTestIncomplete('export query produces queries which fields chagne order in different enironments');
    }

    public function testfill_in_additional_detail_fields()
    {
        $meeting = BeanFactory::newBean('Meetings');

        //preset required attributes
        $meeting->assigned_user_id = 1;
        $meeting->modified_user_id = 1;
        $meeting->created_by = 1;
        $meeting->contact_id = 1;

        $meeting->fill_in_additional_detail_fields();

        //verify effected atributes
        $this->assertEquals('Administrator', $meeting->assigned_user_name);
        $this->assertEquals('Administrator', $meeting->created_by_name);
        $this->assertEquals('Administrator', $meeting->modified_by_name);
        $this->assertTrue(isset($meeting->time_start_hour));
        $this->assertTrue(isset($meeting->date_start));
        $this->assertTrue(isset($meeting->time_start));
        $this->assertTrue(isset($meeting->duration_hours));
        $this->assertTrue(isset($meeting->duration_minutes));
        $this->assertEquals(-1, $meeting->reminder_time);
        $this->assertTrue(isset($meeting->reminder_time));
        $this->assertEquals(false, $meeting->reminder_checked);
        $this->assertEquals(-1, $meeting->email_reminder_time);
        $this->assertEquals(false, $meeting->email_reminder_checked);
        $this->assertEquals('Accounts', $meeting->parent_type);
    }

    public function testget_list_view_data()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $current_theme = SugarThemeRegistry::current();

        //preset required attribute values
        $meeting->status == 'Planned';
        $meeting->parent_type = 'Accounts';
        $meeting->contact_id = 1;
        $meeting->contact_name = 'test';
        $meeting->parent_name = 'Account';

        $expected = array(
                      'DELETED' => 0,
                      'PARENT_TYPE' => 'Accounts',
                      'STATUS' => 'Planned',
                      'TYPE' => 'Sugar',
                      'REMINDER_TIME' => '-1',
                      'EMAIL_REMINDER_TIME' => '-1',
                      'EMAIL_REMINDER_SENT' => '0',
                      'SEQUENCE' => '0',
                      'CONTACT_NAME' => 'test',
                      'PARENT_NAME' => '',
                      'CONTACT_ID' => 1,
                      'REPEAT_INTERVAL' => '1',
                      'PARENT_MODULE' => 'Accounts',
                      'SET_COMPLETE' => '<a id=\'\' onclick=\'SUGAR.util.closeActivityPanel.show("Meetings","","Held","listview","1");\'><img src="themes/'.$current_theme.'/images/close_inline.png?v=fqXdFZ_r6FC1K7P_Fy3mVw"     border=\'0\' alt="Close" /></a>',
                      'DATE_START' => '<font class=\'overdueTask\'></font>',
                      'REMINDER_CHECKED' => false,
                      'EMAIL_REMINDER_CHECKED' => false,
                    );

        $actual = $meeting->get_list_view_data();

        //$this->assertSame($expected, $actual);
        $this->assertEquals($expected['PARENT_TYPE'], $actual['PARENT_TYPE']);
        $this->assertEquals($expected['STATUS'], $actual['STATUS']);
        $this->assertEquals($expected['TYPE'], $actual['TYPE']);
        $this->assertEquals($expected['REMINDER_TIME'], $actual['REMINDER_TIME']);
        $this->assertEquals($expected['EMAIL_REMINDER_TIME'], $actual['EMAIL_REMINDER_TIME']);
        $this->assertEquals($expected['EMAIL_REMINDER_SENT'], $actual['EMAIL_REMINDER_SENT']);
        $this->assertEquals($expected['CONTACT_NAME'], $actual['CONTACT_NAME']);
        $this->assertEquals($expected['CONTACT_ID'], $actual['CONTACT_ID']);
        $this->assertEquals($expected['REPEAT_INTERVAL'], $actual['REPEAT_INTERVAL']);
        $this->assertEquals($expected['PARENT_MODULE'], $actual['PARENT_MODULE']);
    }

    public function testset_notification_body()
    {
        global $current_user;
        $current_user = new User(1);

        $meeting = BeanFactory::newBean('Meetings');

        //test with attributes preset and verify template variables are set accordingly
        $meeting->name = 'test';
        $meeting->status = 'Not Held';
        $meeting->type = 'Sugar';
        $meeting->description = 'test description';
        $meeting->duration_hours = 1;
        $meeting->duration_minutes = 1;
        $meeting->date_start = '2016-02-11 17:30:00';
        $meeting->date_end = '2016-02-11 17:30:00';

        $result = $meeting->set_notification_body(new Sugar_Smarty(), $meeting);

        $this->assertEquals($meeting->name, $result->_tpl_vars['MEETING_SUBJECT']);
        $this->assertEquals($meeting->status, $result->_tpl_vars['MEETING_STATUS']);
        $this->assertEquals('SuiteCRM', $result->_tpl_vars['MEETING_TYPE']);
        $this->assertEquals($meeting->duration_hours, $result->_tpl_vars['MEETING_HOURS']);
        $this->assertEquals($meeting->duration_minutes, $result->_tpl_vars['MEETING_MINUTES']);
        $this->assertEquals($meeting->description, $result->_tpl_vars['MEETING_DESCRIPTION']);
    }

    public function testcreate_notification_email()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $meeting->date_start = '2016-02-11 17:30:00';
        $meeting->date_end = '2016-02-11 17:30:00';

        //test without setting user
        $result = $meeting->create_notification_email(BeanFactory::newBean('Users'));
        $this->assertInstanceOf('SugarPHPMailer', $result);

        //test with valid user
        $result = $meeting->create_notification_email(new User(1));
        $this->assertInstanceOf('SugarPHPMailer', $result);
    }

    public function testsend_assignment_notifications()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $meeting->date_start = '2016-02-11 17:30:00';
        $meeting->date_end = '2016-02-11 17:30:00';

        $admin = BeanFactory::newBean('Administration');
        $admin->retrieveSettings();
        $sendNotifications = false;

        $notify_user = new User(1);

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $meeting->send_assignment_notifications($notify_user, $admin);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testget_meeting_users()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $result = $meeting->get_meeting_users();
        $this->assertTrue(is_array($result));
    }

    public function testget_invite_meetings()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $user = BeanFactory::newBean('Users');
        $result = $meeting->get_invite_meetings($user);
        $this->assertTrue(is_array($result));
    }

    public function testget_notification_recipients()
    {
        $meeting = BeanFactory::newBean('Meetings');

        //test without special_notification
        $result = $meeting->get_notification_recipients();
        $this->assertTrue(is_array($result));

        //test with special_notification
        $meeting->special_notification = 1;
        $result = $meeting->get_notification_recipients();
        $this->assertTrue(is_array($result));
    }

    public function testbean_implements()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $this->assertEquals(false, $meeting->bean_implements('')); //test with blank value
        $this->assertEquals(false, $meeting->bean_implements('test')); //test with invalid value
        $this->assertEquals(true, $meeting->bean_implements('ACL')); //test with valid value
    }

    public function testlistviewACLHelper()
    {
        $meeting = BeanFactory::newBean('Meetings');

        $expected = array('MAIN' => 'a', 'PARENT' => 'a', 'CONTACT' => 'a');
        $actual = $meeting->listviewACLHelper();
        $this->assertSame($expected, $actual);
    }

    public function testsave_relationship_changes()
    {
        $meeting = BeanFactory::newBean('Meetings');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $meeting->save_relationship_changes(false);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    /**
     * This will throw FATAL error on php7
     */
    public function testafterImportSave()
    {
        require_once 'data/Link.php';

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            $meeting = BeanFactory::newBean('Meetings');
            //test without parent_type
            $meeting->afterImportSave();

            //test with parent_type Contacts
            $meeting->parent_type = 'Contacts';
            $meeting->afterImportSave();

            //test with parent_type Leads
            $meeting->parent_type = 'Leads';
            $meeting->afterImportSave();

            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }
    }

    public function testgetDefaultStatus()
    {
        $meeting = BeanFactory::newBean('Meetings');
        $result = $meeting->getDefaultStatus();
        $this->assertEquals('Planned', $result);
    }

    public function testgetMeetingsExternalApiDropDown()
    {
        $actual = getMeetingsExternalApiDropDown();
        $expected = array('Sugar' => 'SuiteCRM');
        $this->assertSame($expected, $actual);
    }

    public function testgetMeetingTypeOptions()
    {
        global $dictionary, $app_list_strings;

        $result = getMeetingTypeOptions($dictionary, $app_list_strings);
        $this->assertTrue(is_array($result));
    }
}
