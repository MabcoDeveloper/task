-- 14/11/19
ALTER TABLE `ost_task` ADD `assignor_id` INT NOT NULL AFTER `staff_id`;



-- 16/11/19
ALTER TABLE `ost_task` ADD `is_private` TINYINT NOT NULL DEFAULT '0' AFTER `assignor_id`;



-- 20/11/19
ALTER TABLE `ost_task` ADD `is_recurring` TINYINT NOT NULL DEFAULT '0' AFTER `is_private`;



-- 10/12/19
ALTER TABLE `ost_ticket` ADD `to_user_id` INT NOT NULL DEFAULT '0' AFTER `user_id`;



-- 14/12/19
--
-- Table structure for table `ost_time_map`
--
CREATE TABLE `ost_time_map` (
  `tm_id` int(11) NOT NULL,
  `tm_staff_id` int(11) NOT NULL,
  `tm_start_time` time NOT NULL,
  `tm_end_time` time NOT NULL,
  `tm_object_type` char(1) NOT NULL
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

--
-- Indexes for table `ost_time_map`
--
ALTER TABLE `ost_time_map`
  ADD PRIMARY KEY (`tm_id`),
  ADD FOREIGN KEY (`tm_staff_id`) REFERENCES ost_staff(`staff_id`);

--
-- AUTO_INCREMENT for table `ost_time_map`
--
ALTER TABLE `ost_time_map`
  MODIFY `tm_id` int(11) NOT NULL AUTO_INCREMENT;



-- 16/12/19
ALTER TABLE `ost_help_topic` ADD `active_sla` INT(2) NOT NULL DEFAULT '15' AFTER `sla_id`; 



-- 25/12/19
ALTER TABLE `ost_task` DROP COLUMN `is_recurring`;



-- 02/01/20
CREATE TABLE `ost_recurring_tasks` (
  `rt_id` int(11) NOT NULL,
  `rt_title` varchar(1000) NOT NULL,
  `rt_period` varchar(50) NOT NULL,
  `rt_assignor_id` int(11) NOT NULL,
  `rt_staff_id` int(11) NOT NULL,
  `rt_dept_id` int(11) NOT NULL,
  `is_private` tinyint(4) NOT NULL DEFAULT 0,
  `is_active` int(11) NOT NULL DEFAULT 1,
  `start_time` time NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 30
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

--
-- Indexes for table `ost_recurring_tasks`
--
ALTER TABLE `ost_recurring_tasks`
  ADD PRIMARY KEY (`rt_id`);

--
-- AUTO_INCREMENT for table `ost_recurring_tasks`
--
ALTER TABLE `ost_recurring_tasks`
  MODIFY `rt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT = 1;



-- 02/01/20
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateRecurringTask`(IN `TaskTitle` VARCHAR(300), IN `Body` TEXT CHARSET utf8, IN `IsPrivate` INT, IN `DeptID` INT, IN `AssignorID` INT, IN `Duration` INT, IN `StartTime` TIME, IN `StaffID` INT, IN `TeamID` INT)
BEGIN
    SET @TaskSequenceId = (SELECT `value` FROM `ost_config` WHERE `key` = 'task_sequence_id');

	SET @NewSequenceNum = (SELECT `next` FROM `ost_sequence` WHERE `id` = @TaskSequenceId);

	SET @NewDuration = Duration - 720; -- In Minutes (To solve a time different bug)

	INSERT INTO `ost_task`      (`number`       , `dept_id`    , `staff_id`, `assignor_id`, `is_private`, `team_id`, `flags`, `duedate`                                                                , `created`                                                        , `updated`) VALUES 
								(@NewSequenceNum, DeptID       , StaffID   , AssignorID   , IsPrivate   , TeamID   , 1      , DATE_ADD(CONCAT(UTC_DATE(), ' ', StartTime), INTERVAL @NewDuration MINUTE), DATE_ADD(CONCAT(UTC_DATE(), ' ', StartTime), INTERVAL -720 MINUTE), DATE_ADD(CONCAT(UTC_DATE(), ' ', StartTime), INTERVAL -720 MINUTE));

	SET @NewTaskID = (SELECT LAST_INSERT_ID());

	SET @NewTaskTitle = TaskTitle;

	INSERT INTO `ost_task__cdata`  (`task_id` , `title`) VALUES 
								   (@NewTaskID, @NewTaskTitle);


	INSERT INTO `ost_thread`       (`object_id`, `object_type`, `created`) VALUES 
								   (@NewTaskID , 'A'          , NOW());

	SET @LastThreadID = (SELECT LAST_INSERT_ID());
	SET @AssignorName = (SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `staff_id` = AssignorID);
	SET @StaffName =    (SELECT CONCAT(`firstname`, ' ', `lastname`) FROM `ost_staff` WHERE `staff_id` = StaffID);


	INSERT INTO `ost_thread_entry` (`id`, `pid`, `thread_id`  , `staff_id`  , `user_id`, `type`, `flags`, `poster`       , `editor`, `editor_type`, `source`, `title`                   , `body`       , `format`, `ip_address`, `recipients`  , `created`, `updated`) VALUES 
								   (NULL, '0'  , @LastThreadID, AssignorID  , '0'      , 'M'   , '64'   , @AssignorName  , NULL    , NULL         , ''      , NULL                      , Body         , 'html'  , '::1'       , NULL          , NOW()    , NOW());

	SET @LastThreadEntryId = (SELECT LAST_INSERT_ID());


	INSERT INTO `ost_thread_event` (`id`, `thread_id`  , `event_id`  , `staff_id`, `team_id`, `dept_id`, `topic_id`, `data`, `username`, `uid`, `uid_type`, `annulled`, `timestamp`) VALUES 
								   (NULL, @LastThreadID, '1'         , '0'       , '0'      , DeptID   , '0'       , NULL  , 'SYSTEM'  , AssignorID  , 'S'       , '0'       , NOW()); 

	INSERT INTO `ost_thread_event` (`id`, `thread_id`  , `event_id`  , `staff_id`, `team_id`, `dept_id`, `topic_id`, `data`                                                                                                                      , `username`, `uid`       , `uid_type`  , `annulled`, `timestamp`) VALUES 
								   (NULL, @LastThreadID, '4'         , AssignorID, '0'      , DeptID   , '0'       , CONCAT('{"staff":[', StaffID, ',{"format":"full","parts":{"first":"SYSTEM","last":"SYSTEM"},"name":"SYSTEM SYSTEM"}]}')     , 'SYSTEM'  , AssignorID  , 'S'         , '0'       , NOW());

	INSERT INTO `ost_form_entry` (`id`, `form_id`, `object_id`, `object_type`, `sort`, `extra`, `created`            , `updated`) VALUES 
								 (NULL, '5'      , @NewTaskID, 'A'           , '1'   , NULL   , NOW()                , NOW());

	SET @LastFormEntryId = (SELECT LAST_INSERT_ID());


	INSERT INTO `ost_form_entry_values` (`entry_id`      , `field_id`, `value`      , `value_id`) VALUES 
										(@LastFormEntryId, '32'      , @NewTaskTitle, NULL);

	INSERT INTO `ost__search` (`object_type`, `object_id`       , `title`, `content`) VALUES 
							  ('H'          , @LastThreadEntryId, ''     , @NewTaskTitle);

	UPDATE `ost_sequence` SET `next` = `next` + 1 WHERE `id` = @TaskSequenceId;
END$$
DELIMITER ;



-- 02/01/20
ALTER TABLE `ost_staff` ADD `check_login` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `updated`;



-- 08/01/20
ALTER TABLE `ost_recurring_tasks` ADD `rt_team_id` INT NULL DEFAULT '0' AFTER `rt_staff_id`;



-- 09/01/20
--
-- Table structure for table `ost_agent_users_tickets`
--

CREATE TABLE `ost_agent_users_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Indexes for table `ost_agent_users_tickets`
--
ALTER TABLE `ost_agent_users_tickets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `ost_agent_users_tickets`
--
ALTER TABLE `ost_agent_users_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



-- 12/01/20
CREATE TABLE `ost_agent_users_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

--
-- Dumping data for table `ost_agent_users_tickets`
--

INSERT INTO `ost_agent_users_tickets` (`id`, `user_id`, `staff_id`) VALUES
(1, 14, 50),
(2, 13, 50),
(3, 15, 50),
(4, 19, 50),
(5, 20, 50),
(6, 25, 50),
(7, 28, 50),
(8, 12, 49),
(9, 23, 49),
(10, 11, 49),
(11, 16, 49),
(12, 21, 49),
(13, 26, 49),
(14, 27, 49),
(15, 30, 49),
(16, 10, 49),
(17, 17, 49),
(18, 24, 49),
(20, 41, 40),
(21, 42, 40),
(22, 45, 40),
(23, 43, 40),
(24, 47, 40),
(25, 32, 55),
(26, 34, 55),
(27, 35, 55),
(28, 33, 55),
(29, 48, 40),
(30, 49, 40),
(31, 46, 40),
(32, 36, 62),
(33, 37, 62),
(34, 38, 62),
(35, 22, 62),
(36, 40, 39),
(37, 39, 39),
(38, 44, 40),
(39, 31, 49);

--
-- Indexes for table `ost_agent_users_tickets`
--
ALTER TABLE `ost_agent_users_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for table `ost_agent_users_tickets`
--
ALTER TABLE `ost_agent_users_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT = 40;



-- 13/01/20
ALTER TABLE `ost_recurring_tasks` ADD `rt_body` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `rt_title`; 



-- 14/01/20
ALTER TABLE `ost_staff` ADD `fcm_token` VARCHAR(1500) NULL AFTER `check_login`, ADD UNIQUE (`fcm_token`);



-- 27/01/20
--
-- Table structure for table `ost_help_topic_flow`
--

CREATE TABLE `ost_help_topic_flow` (
  `id` int(11) NOT NULL,
  `help_topic_id` int(11) NOT NULL,
  `step_number` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL
) ENGINE = MyISAM DEFAULT CHARSET = utf8;

--
-- Indexes for table `ost_help_topic_flow`
--
ALTER TABLE `ost_help_topic_flow`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `ost_help_topic_flow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT = 1;

--
-- Dumping data for table `ost_help_topic_flow`
--

INSERT INTO `ost_help_topic_flow` (`help_topic_id`, `step_number`, `staff_id`) VALUES
(74, 1, 28),
(74, 1, 29),
(74, 1, 31),
(74, 2, 64),
(74, 2, 36),
(74, 2, 35),
(74, 3, 51),
(46, 1, 29),
(46, 1, 28),
(46, 1, 31),
(46, 2, 6),
(40, 1, 29),
(40, 1, 28),
(40, 1, 31),
(40, 2, 6),
(85, 1, 31),
(85, 2, 6),
(45, 1, 6),
(44, 1, 29),
(44, 1, 28),
(44, 1, 31),
(44, 2, 6),
(43, 1, 29),
(43, 1, 28),
(43, 1, 31),
(43, 2, 6),
(59, 1, 29),
(59, 1, 28),
(59, 1, 31),
(59, 2, 58),
(59, 3, 6),
(59, 4, 26),
(59, 4, 65),
(59, 5, 54),
(59, 5, 53),
(81, 1, 29),
(81, 1, 28),
(81, 1, 31),
(81, 2, 39),
(81, 3, 6),
(81, 4, 26),
(81, 4, 65),
(81, 5, 54),
(81, 5, 53),
(82, 1, 29),
(82, 1, 28),
(82, 1, 31),
(82, 2, 40),
(82, 3, 6),
(82, 4, 26),
(82, 4, 65),
(82, 5, 54),
(82, 5, 53),
(83, 1, 29),
(83, 1, 28),
(83, 1, 31),
(83, 2, 56),
(83, 2, 55),
(83, 3, 6),
(83, 4, 26),
(83, 4, 65),
(83, 5, 54),
(83, 5, 53),
(75, 1, 29),
(75, 1, 28),
(75, 1, 31),
(75, 2, 64),
(75, 2, 36),
(75, 2, 35),
(74, 1, 29),
(74, 1, 28),
(74, 1, 31),
(74, 2, 64),
(74, 2, 36),
(74, 2, 35),
(74, 3, 51),
(38, 1, 6),
(39, 1, 6),
(58, 1, 29),
(58, 1, 28),
(58, 1, 31),
(58, 2, 52),
(58, 2, 39),
(58, 3, 6),
(58, 4, 52),
(58, 5, 54),
(58, 5, 53),
(61, 1, 29),
(61, 1, 28),
(61, 1, 31),
(61, 2, 52),
(61, 2, 40),
(61, 3, 6),
(61, 4, 52),
(61, 5, 54),
(61, 5, 53),
(60, 1, 29),
(60, 1, 28),
(60, 1, 31),
(60, 2, 52),
(60, 2, 55),
(60, 2, 56),
(60, 3, 6),
(60, 4, 52),
(60, 5, 54),
(60, 5, 53),
(86, 1, 29),
(86, 1, 28),
(86, 1, 31),
(86, 2, 52),
(86, 2, 62),
(86, 3, 6),
(86, 4, 52),
(86, 5, 54),
(86, 5, 53),
(68, 1, 29),
(68, 1, 28),
(68, 1, 31),
(68, 2, 52),
(68, 3, 6),
(68, 4, 52),
(68, 5, 54),
(68, 5, 53),
(49, 1, 38),
(49, 2, 6),
(51, 1, 47),
(51, 2, 6),
(79, 1, 28),
(79, 1, 29),
(79, 1, 31),
(79, 2, 40),
(79, 2, 21),
(79, 2, 71),
(79, 3, 6),
(79, 4, 21),
(79, 5, 54),
(79, 5, 53),
(80, 1, 28),
(80, 1, 29),
(80, 1, 31),
(80, 2, 39),
(80, 2, 21),
(80, 2, 71),
(80, 3, 6),
(80, 4, 21),
(80, 5, 54),
(80, 5, 53),
(53, 1, 28),
(53, 1, 29),
(53, 1, 31),
(53, 2, 56),
(53, 2, 55),
(53, 2, 21),
(53, 2, 71),
(53, 3, 6),
(53, 4, 21),
(53, 5, 54),
(53, 5, 53),
(67, 1, 28),
(67, 1, 29),
(67, 1, 31),
(67, 2, 58),
(67, 2, 21),
(67, 2, 71),
(67, 3, 6),
(67, 4, 21),
(67, 5, 54),
(67, 5, 53),
(64, 1, 6),
(72, 1, 26),
(72, 2, 6),
(71, 1, 21),
(71, 2, 6),
(70, 1, 52),
(70, 2, 6),
(65, 1, 6),
(52, 1, 49),
(52, 1, 50),
(52, 2, 6),
(73, 1, 57),
(73, 1, 14),
(73, 2, 6),
(73, 3, 59),
(73, 4, 63),
(76, 1, 51),
(76, 2, 29),
(76, 2, 31),
(76, 3, 46),
(57, 1, 26),
(57, 1, 52),
(57, 2, 54),
(57, 2, 53),
(57, 2, 18),
(57, 3, 26),
(57, 3, 52),
(55, 1, 21),
(55, 2, 54),
(55, 2, 53),
(55, 2, 18),
(55, 3, 21),
(17, 1, 17),
(17, 1, 5),
(17, 1, 24),
(16, 1, 17),
(16, 1, 5),
(16, 1, 24),
(15, 1, 17),
(15, 1, 5),
(15, 1, 24),
(26, 1, 6),
(34, 1, 6),
(29, 1, 6),
(30, 1, 17),
(30, 1, 5),
(94, 1, 17);



-- 28/01/20
ALTER TABLE `ost_ticket` ADD `current_step` INT NULL DEFAULT '1' AFTER `isanswered`;

ALTER TABLE `ost_recurring_tasks` ADD `event_name` VARCHAR(100) NOT NULL DEFAULT '0' AFTER `duration`;

ALTER TABLE `ost_recurring_tasks` CHANGE `start_time` `start_recurring_date` DATETIME NOT NULL;

DROP PROCEDURE IF EXISTS `CreateRecurringTask`;



-- 07/03/20
ALTER TABLE `ost_staff` ADD `isreportsadmin` TINYINT(1) NOT NULL DEFAULT '0' AFTER `isadmin`; 



-- 08/03/20
ALTER TABLE `ost_task` ADD `collab_1` INT NOT NULL DEFAULT '0' AFTER `flags`;
ALTER TABLE `ost_task` ADD `collab_2` INT NOT NULL DEFAULT '0' AFTER `collab_1`;
ALTER TABLE `ost_task` ADD `collab_3` INT NOT NULL DEFAULT '0' AFTER `collab_2`;



-- 09/03/20
INSERT INTO `ost_help_topic_flow` (`help_topic_id`, `step_number`, `staff_id`) VALUES
(103, 1, 81),
(103, 1, 83),
(103, 1, 54),
(103, 1, 18),
(103, 1, 63),
(103, 1, 82),
(103, 1, 73),
(103, 1, 53),
(104, 1, 81),
(104, 1, 83),
(104, 1, 54),
(104, 1, 18),
(104, 1, 63),
(104, 1, 82),
(104, 1, 73),
(104, 1, 53),
(105, 1, 81),
(105, 1, 83),
(105, 1, 54),
(105, 1, 18),
(105, 1, 63),
(105, 1, 82),
(105, 1, 73),
(105, 1, 53),
(106, 1, 81),
(106, 1, 83),
(106, 1, 54),
(106, 1, 18),
(106, 1, 63),
(106, 1, 82),
(106, 1, 73),
(106, 1, 53),
(107, 1, 81),
(107, 1, 83),
(107, 1, 54),
(107, 1, 18),
(107, 1, 63),
(107, 1, 82),
(107, 1, 73),
(107, 1, 53),
(108, 1, 81),
(108, 1, 83),
(108, 1, 54),
(108, 1, 18),
(108, 1, 63),
(108, 1, 82),
(108, 1, 73),
(108, 1, 53),
(109, 1, 81),
(109, 1, 83),
(109, 1, 54),
(109, 1, 18),
(109, 1, 63),
(109, 1, 82),
(109, 1, 73),
(109, 1, 53),
(98, 1, 14),
(98, 2, 81),
(98, 2, 83),
(98, 2, 54),
(98, 2, 18),
(98, 2, 63),
(98, 2, 82),
(98, 2, 73),
(98, 2, 53),
(99, 1, 14),
(99, 2, 81),
(99, 2, 83),
(99, 2, 54),
(99, 2, 18),
(99, 2, 63),
(99, 2, 82),
(99, 2, 73),
(99, 2, 53),
(100, 1, 14),
(100, 2, 81),
(100, 2, 83),
(100, 2, 54),
(100, 2, 18),
(100, 2, 63),
(100, 2, 82),
(100, 2, 73),
(100, 2, 53),
(101, 1, 14),
(101, 2, 81),
(101, 2, 83),
(101, 2, 54),
(101, 2, 18),
(101, 2, 63),
(101, 2, 82),
(101, 2, 73),
(101, 2, 53),
(102, 1, 14),
(102, 2, 81),
(102, 2, 83),
(102, 2, 54),
(102, 2, 18),
(102, 2, 63),
(102, 2, 82),
(102, 2, 73),
(102, 2, 53)



-- 15/03/20
ALTER TABLE `ost_faq` ADD `creator_staff_id` INT NOT NULL DEFAULT '23' AFTER `ispublished`;
ALTER TABLE `ost_faq_category` ADD `creator_staff_id` INT NOT NULL DEFAULT '23' AFTER `name`;
UPDATE `ost_staff` SET `permissions`='{"faq.manage":1}';



--  02/06/20
CREATE TABLE `ost_organization__teams` (
  `id` int(11) NOT NULL,
  `org_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for table `ost_organization__teams`
--
ALTER TABLE `ost_organization__teams`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `ost_organization__teams`
--
ALTER TABLE `ost_organization__teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;