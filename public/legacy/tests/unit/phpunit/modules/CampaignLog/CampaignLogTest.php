<?php

use SuiteCRM\Test\SuitePHPUnitFrameworkTestCase;

class CampaignLogTest extends SuitePHPUnitFrameworkTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $current_user;
        get_sugar_config_defaults();
        $current_user = BeanFactory::newBean('Users');
    }

    public function testCampaignLog()
    {
        // Execute the constructor and check for the Object type and  attributes
        $campaignLog = BeanFactory::newBean('CampaignLog');
        $this->assertInstanceOf('CampaignLog', $campaignLog);
        $this->assertInstanceOf('SugarBean', $campaignLog);

        $this->assertAttributeEquals('CampaignLog', 'module_dir', $campaignLog);
        $this->assertAttributeEquals('CampaignLog', 'object_name', $campaignLog);
        $this->assertAttributeEquals('campaign_log', 'table_name', $campaignLog);
        $this->assertAttributeEquals(true, 'new_schema', $campaignLog);
    }

    public function testget_list_view_data()
    {
        $campaignLog = BeanFactory::newBean('CampaignLog');

        //execute the method and verify it returns an array
        $actual = $campaignLog->get_list_view_data();
        $this->assertTrue(is_array($actual));
        $this->assertSame(array(), $actual);
    }

    public function testretrieve_email_address()
    {
        $campaignLog = BeanFactory::newBean('CampaignLog');
        $actual = $campaignLog->retrieve_email_address();
        $this->assertGreaterThanOrEqual('', $actual);
    }

    public function testget_related_name()
    {
        $campaignLog = BeanFactory::newBean('CampaignLog');

        //execute the method and verify that it retunrs expected results for all type parameters
        $this->assertEquals('1Emails', $campaignLog->get_related_name(1, 'Emails'));
        $this->assertEquals('1Contacts', $campaignLog->get_related_name(1, 'Contacts'));
        $this->assertEquals('1Leads', $campaignLog->get_related_name(1, 'Leads'));
        $this->assertEquals('1Prospects', $campaignLog->get_related_name(1, 'Prospects'));
        $this->assertEquals('1CampaignTrackers', $campaignLog->get_related_name(1, 'CampaignTrackers'));
        $this->assertEquals('1Accounts', $campaignLog->get_related_name(1, 'Accounts'));
    }
}
