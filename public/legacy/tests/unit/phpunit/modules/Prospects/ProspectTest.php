<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class ProspectTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testProspect()
    {
        // Execute the constructor and check for the Object type and  attributes
        $prospect = BeanFactory::newBean('Prospects');

        $this->assertInstanceOf('Prospect', $prospect);
        $this->assertInstanceOf('Person', $prospect);
        $this->assertInstanceOf('SugarBean', $prospect);

        $this->assertAttributeEquals('prospects', 'table_name', $prospect);
        $this->assertAttributeEquals('Prospects', 'module_dir', $prospect);
        $this->assertAttributeEquals('Prospect', 'object_name', $prospect);

        $this->assertAttributeEquals(true, 'new_schema', $prospect);
        $this->assertAttributeEquals(true, 'importable', $prospect);
    }

    public function testfill_in_additional_list_fields()
    {
        $prospect = BeanFactory::newBean('Prospects');

        $prospect->salutation = 'mr';
        $prospect->title = 'lastn firstn';
        $prospect->first_name = 'first';
        $prospect->first_name = 'last';
        $prospect->email1 = 'email1@test.com';

        $prospect->fill_in_additional_list_fields();

        $this->assertAttributeEquals('last', 'full_name', $prospect);
        $this->assertAttributeEquals('last &lt;email1@test.com&gt;', 'email_and_name1', $prospect);
    }

    public function testfill_in_additional_detail_fields()
    {
        $prospect = BeanFactory::newBean('Prospects');

        $prospect->salutation = 'mr';
        $prospect->title = 'lastn firstn';
        $prospect->first_name = 'first';
        $prospect->first_name = 'last';
        $prospect->email1 = 'email1@test.com';

        $prospect->fill_in_additional_detail_fields();

        $this->assertAttributeEquals('last', 'full_name', $prospect);
    }

    public function testbuild_generic_where_clause()
    {
        $prospect = BeanFactory::newBean('Prospects');

        //test with empty string params
        $expected = "prospects.last_name like '%' or prospects.first_name like '%' or prospects.assistant like '%'";
        $actual = $prospect->build_generic_where_clause('');
        $this->assertSame($expected, $actual);

        //test with valid string params
        $expected = "prospects.last_name like '1%' or prospects.first_name like '1%' or prospects.assistant like '1%' or prospects.phone_home like '%1%' or prospects.phone_mobile like '%1%' or prospects.phone_work like '%1%' or prospects.phone_other like '%1%' or prospects.phone_fax like '%1%' or prospects.assistant_phone like '%1%'";
        $actual = $prospect->build_generic_where_clause('1');
        $this->assertSame($expected, $actual);
    }

    public function testconverted_prospect()
    {
        $prospect = BeanFactory::newBean('Prospects');

        // Execute the method and test that it works and doesn't throw an exception.
        try {
            //$prospect->converted_prospect('1', '2', '3', '4');
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail($e->getMessage() . "\nTrace:\n" . $e->getTraceAsString());
        }

        $this->markTestIncomplete('Multiple errors in query');
    }

    public function testbean_implements()
    {
        $prospect = BeanFactory::newBean('Prospects');

        $this->assertEquals(false, $prospect->bean_implements('')); //test with blank value
        $this->assertEquals(false, $prospect->bean_implements('test')); //test with invalid value
        $this->assertEquals(true, $prospect->bean_implements('ACL')); //test with valid value
    }

    public function testretrieveTargetList()
    {
        $prospect = BeanFactory::newBean('Prospects');

        $result = $prospect->retrieveTargetList('', array('id', 'first_name'), 0, 1, 1, 0, 'Accounts');
        $this->assertTrue(is_array($result));
    }

    public function testretrieveTarget()
    {
        $prospect = BeanFactory::newBean('Prospects');

        $result = $prospect->retrieveTarget('');
        $this->assertEquals(null, $result);
    }

    public function testget_unlinked_email_query()
    {
        self::markTestIncomplete('environment dependency (CRLF2)');

        $prospect = BeanFactory::newBean('Prospects');

        $expected = "SELECT emails.id FROM emails  JOIN (select DISTINCT email_id from emails_email_addr_rel eear\n\n	join email_addr_bean_rel eabr on eabr.bean_id ='' and eabr.bean_module = 'Prospects' and\n	eabr.email_address_id = eear.email_address_id and eabr.deleted=0\n	where eear.deleted=0 and eear.email_id not in\n	(select eb.email_id from emails_beans eb where eb.bean_module ='Prospects' and eb.bean_id = '')\n	) derivedemails on derivedemails.email_id = emails.id";
        $actual = $prospect->get_unlinked_email_query();
        $this->assertSame($expected, $actual);
    }
}
