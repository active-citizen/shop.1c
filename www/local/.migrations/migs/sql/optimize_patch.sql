ALTER TABLE `b_iblock_element` ADD KEY `TIMESTAMP_X` (`TIMESTAMP_X`);
ALTER TABLE `b_iblock_element` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `XML_ID` (`XML_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `WF_PARENT_ELEMENT_ID` (`WF_PARENT_ELEMENT_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `MODIFIED_BY`(`MODIFIED_BY`);
ALTER TABLE `b_iblock_element` ADD KEY `DATE_CREATE` (`DATE_CREATE`);
ALTER TABLE `b_iblock_element` ADD KEY `CREATED_BY` (`CREATED_BY`);
ALTER TABLE `b_iblock_element` ADD KEY `IBLOCK_SECTION_ID` (`IBLOCK_SECTION_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `ACTIVE` (`ACTIVE`);
ALTER TABLE `b_iblock_element` ADD KEY `ACTIVE_FROM` (`ACTIVE_FROM`);
ALTER TABLE `b_iblock_element` ADD KEY `ACTIVE_TO` (`ACTIVE_TO`);
ALTER TABLE `b_iblock_element` ADD KEY `SORT` (`SORT`); 
ALTER TABLE `b_iblock_element` ADD KEY `NAME` (`NAME`);
ALTER TABLE `b_iblock_element` ADD KEY `DETAIL_PICTURE` (`DETAIL_PICTURE`);
ALTER TABLE `b_iblock_element` ADD KEY `WF_STATUS_ID` (`WF_STATUS_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `WF_NEW` (`WF_NEW`);
ALTER TABLE `b_iblock_element` ADD KEY `WF_LOCKED_BY` (`WF_LOCKED_BY`); 
ALTER TABLE `b_iblock_element` ADD KEY `WF_DATE_LOCK` (`WF_DATE_LOCK`);
ALTER TABLE `b_iblock_element` ADD KEY `IN_SECTIONS` (`IN_SECTIONS`);
ALTER TABLE `b_iblock_element` ADD KEY `CODE` (`CODE`); 
ALTER TABLE `b_iblock_element` ADD KEY `TAGS` (`TAGS`);
ALTER TABLE `b_iblock_element` ADD KEY `TMP_ID` (`TMP_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `WF_LAST_HISTORY_ID` (`WF_LAST_HISTORY_ID`);
ALTER TABLE `b_iblock_element` ADD KEY `SHOW_COUNTER` (`SHOW_COUNTER`);
ALTER TABLE `b_iblock_element` ADD KEY `SHOW_COUNTER_START` (`SHOW_COUNTER_START`); 


ALTER TABLE `b_iblock_element_iprop` ADD KEY `ELEMENT_ID` (`ELEMENT_ID`);
ALTER TABLE `b_iblock_element_iprop` ADD KEY `IPROP_ID` (`IPROP_ID`); 


ALTER TABLE `b_iblock_property` ADD KEY `TIMESTAMP_X` (`TIMESTAMP_X`);
ALTER TABLE `b_iblock_property` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_property` ADD KEY `NAME` (`NAME`);
ALTER TABLE `b_iblock_property` ADD KEY `ACTIVE` (`ACTIVE`);
ALTER TABLE `b_iblock_property` ADD KEY `SORT` (`SORT`);
ALTER TABLE `b_iblock_property` ADD KEY `PROPERTY_TYPE` (`PROPERTY_TYPE`);
ALTER TABLE `b_iblock_property` ADD KEY `ROW_COUNT` (`ROW_COUNT`); 
ALTER TABLE `b_iblock_property` ADD KEY `COL_COUNT` (`COL_COUNT`); 
ALTER TABLE `b_iblock_property` ADD KEY `LIST_TYPE` (`LIST_TYPE`);
ALTER TABLE `b_iblock_property` ADD KEY `MULTIPLE` (`MULTIPLE`);
ALTER TABLE `b_iblock_property` ADD KEY `XML_ID` (`XML_ID`);
ALTER TABLE `b_iblock_property` ADD KEY `FILE_TYPE` (`FILE_TYPE`);
ALTER TABLE `b_iblock_property` ADD KEY `MULTIPLE_CNT` (`MULTIPLE_CNT`);
ALTER TABLE `b_iblock_property` ADD KEY `TMP_ID` (`TMP_ID`);
ALTER TABLE `b_iblock_property` ADD KEY `WITH_DESCRIPTION` (`WITH_DESCRIPTION`);
ALTER TABLE `b_iblock_property` ADD KEY `SEARCHABLE` (`SEARCHABLE`);
ALTER TABLE `b_iblock_property` ADD KEY `FILTRABLE` (`FILTRABLE`);
ALTER TABLE `b_iblock_property` ADD KEY `IS_REQUIRED` (`IS_REQUIRED`);
ALTER TABLE `b_iblock_property` ADD KEY `VERSION` (`VERSION`);
ALTER TABLE `b_iblock_property` ADD KEY `USER_TYPE` (`USER_TYPE`); 
ALTER TABLE `b_iblock_property` ADD KEY `HINT` (`HINT`); 

ALTER TABLE `b_iblock_property_enum` ADD KEY `PROPERTY_ID` (`PROPERTY_ID`);
ALTER TABLE `b_iblock_property_enum` ADD KEY `DEF` (`DEF`);
ALTER TABLE `b_iblock_property_enum` ADD KEY `SORT` (`SORT`);
ALTER TABLE `b_iblock_property_enum` ADD KEY `XML_ID` (`XML_ID`);

ALTER TABLE `b_iblock_iproperty` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_iproperty` ADD KEY `CODE` (`CODE`);
ALTER TABLE `b_iblock_iproperty` ADD KEY `ENTITY_TYPE` (`ENTITY_TYPE`);
ALTER TABLE `b_iblock_iproperty` ADD KEY `ENTITY_ID` (`ENTITY_ID`);

-- -------------------------------------------------
ALTER TABLE `b_iblock_section` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_section` ADD KEY `TIMESTAMP_X` (`TIMESTAMP_X`);
ALTER TABLE `b_iblock_section` ADD KEY `MODIFIED_BY` (`MODIFIED_BY`);
ALTER TABLE `b_iblock_section` ADD KEY `DATE_CREATE` (`DATE_CREATE`);
ALTER TABLE `b_iblock_section` ADD KEY `CREATED_BY` (`CREATED_BY`);
ALTER TABLE `b_iblock_section` ADD KEY `IBLOCK_SECTION_ID` (`IBLOCK_SECTION_ID`);
ALTER TABLE `b_iblock_section` ADD KEY `ACTIVE` (`ACTIVE`);
ALTER TABLE `b_iblock_section` ADD KEY `GLOBAL_ACTIVE` (`GLOBAL_ACTIVE`);
ALTER TABLE `b_iblock_section` ADD KEY `SORT` (`SORT`);
ALTER TABLE `b_iblock_section` ADD KEY `DEPTH_LEVEL` (`DEPTH_LEVEL`);
ALTER TABLE `b_iblock_section` ADD KEY `CODE` (`CODE`);
ALTER TABLE `b_iblock_section` ADD KEY `XML_ID` (`XML_ID`);
ALTER TABLE `b_iblock_section` ADD KEY `TMP_ID` (`TMP_ID`);
ALTER TABLE `b_iblock_section` ADD KEY `SOCNET_GROUP_ID` (`SOCNET_GROUP_ID`);

ALTER TABLE `b_iblock_section_element` ADD KEY `IBLOCK_SECTION_ID` (`IBLOCK_SECTION_ID`);
ALTER TABLE `b_iblock_section_element` ADD KEY `ADDITIONAL_PROPERTY_ID` (`ADDITIONAL_PROPERTY_ID`);

ALTER TABLE `b_iblock_section_property` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_section_property` ADD KEY `DISPLAY_TYPE` (`DISPLAY_TYPE`);
ALTER TABLE `b_iblock_section_property` ADD KEY `SMART_FILTER` (`SMART_FILTER`);
ALTER TABLE `b_iblock_section_property` ADD KEY `DISPLAY_EXPANDED` (`DISPLAY_EXPANDED`);

ALTER TABLE `b_iblock_section_right` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_section_right` ADD KEY `SECTION_ID` (`SECTION_ID`);
ALTER TABLE `b_iblock_section_right` ADD KEY `RIGHT_ID` (`RIGHT_ID`);
ALTER TABLE `b_iblock_section_right` ADD KEY `IS_INHERITED` (`IS_INHERITED`);

ALTER TABLE `b_iblock_section_iprop` ADD KEY `SECTION_ID` (`SECTION_ID`);

ALTER TABLE `b_option` ADD KEY `MODULE_ID` (`MODULE_ID`);
ALTER TABLE `b_option` ADD KEY `SITE_ID` (`SITE_ID`);

ALTER TABLE `b_iblock_fields` ADD KEY `IBLOCK_ID` (`IBLOCK_ID`);
ALTER TABLE `b_iblock_fields` ADD KEY `FIELD_ID` (`FIELD_ID`);
ALTER TABLE `b_iblock_fields` ADD KEY `IS_REQUIRED` (`IS_REQUIRED`);

ALTER TABLE `b_user` ADD KEY `LOGIN` (`LOGIN`);
ALTER TABLE `b_user` ADD KEY `ACTIVE` (`ACTIVE`);
ALTER TABLE `b_user` ADD KEY `LID` (`LID`);
ALTER TABLE `b_user` ADD KEY `DATE_REGISTER` (`DATE_REGISTER`);
ALTER TABLE `b_user` ADD KEY `TIMESTAMP_X` (`TIMESTAMP_X`);
