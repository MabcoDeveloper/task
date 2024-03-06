<?php

class ReportModel {
    const PERM_AGENTS = 'stats.agents';

    static protected $perms = array(
        self::PERM_AGENTS => array(
            'title' =>
            /* @trans */ 'Stats',
            'desc'  =>
            /* @trans */ 'Ability to view stats of other agents in allowed departments',
            'primary' => true,
        )
    );

    static function getPermissions() {
        return self::$perms;
    }
}

RolePermission::register(/* @trans */ 'Miscellaneous', ReportModel::getPermissions());

class OverviewReport {
    var $start;
    var $end;
    var $format;

    function __construct($start, $end = 'now', $format = null) {
        global $cfg;
        $this->start = $start;
        $this->end = $end;
        $this->format = $format ?: $cfg->getDateFormat(true);
    }

    function getStartDate($format=null, $translate=true) {
        if (!$this->start)
            return '';

        $format =  $format ?: $this->format;
        if ($translate) {
            $format = str_replace(
                array('y', 'Y', 'm'),
                array('yy', 'yyyy', 'mm'),
                $format);
        }

        return Format::date(Misc::dbtime($this->start), false, $format);
    }

    function getDateRange() {
        global $cfg;
        $start = $this->start ?: '3/7/20';
        $stop = $this->end ?: 'now';
        // Convert user time to db time
        $start = Misc::dbtime($start);

        // Stop time can be relative.
        if ($stop[0] == '+') {
            // $start time + time(X days)
            $now = time();
            $stop = $start + (strtotime($stop, $now)-$now);
        } else {
            $stop = Misc::dbtime($stop);
        }

        $start = "FROM_UNIXTIME($start)"; //1583100000, 1577900000 = January 1, 2020
        $stop  = "FROM_UNIXTIME($stop)";
        return array($start, $stop);
    }

    function enumTabularGroups() {
        return array(
            "topic"=>__("All Topics"),
            "staff"=>__("All Agents"),
            "staff_ht"=>__("Help Topic / Agents")
        );
    }

    function getTabularData($group='dept') {
        global $thisstaff;
        $event_ids = Event::getIds();
        
        $event = function ($name) use ($event_ids) {
            return $event_ids[$name];
        };

        list($start, $stop) = $this->getDateRange();

        $times = ThreadEvent::objects()
        ->constrain(array(
            'thread__entries' => array(
                'thread__entries__type' => 'R',
                ),
            ))
        ->constrain(array(
            'thread__events' => array(
                'thread__events__event_id' => $event('Done'),
                'event_id' => $event('Done'),
                'annulled' => 0,
                'timestamp__range' => array($start, $stop, true),
            ),
        ))
        ->filter(array(
                'timestamp__range' => array($start, $stop, true),
            ))
        ->aggregate(array(
            'ServiceTime' => SqlAggregate::AVG(SqlFunction::timestampdiff(
                new SqlCode('MINUTE'), new SqlField('thread__events__timestamp'), new SqlField('timestamp'))
            ),
            'ResponseTime' => SqlAggregate::AVG(SqlFunction::timestampdiff(
                new SqlCode('MINUTE'),new SqlField('thread__entries__parent__created'), new SqlField('thread__entries__created')
            )),
        ));

        // if (isset($_REQUEST['ht_id']) && $_REQUEST['ht_id'] !== '') {
        //     $times->filter(array(
        //         'thread__events__topic_id' => $_REQUEST['ht_id'],
        //     ));
        // }

        $stats = ThreadEvent::objects()
        ->filter(array(
                'annulled' => 0,
                'timestamp__range' => array($start, $stop, true),
                'thread__object_type' => 'T',
            ))
        ->aggregate(array(
            'Opened' => SqlAggregate::COUNT(
                SqlCase::N()
                    ->when(new Q(array('event_id' => $event('created'))), 1)
            ),
            'Assigned' => SqlAggregate::COUNT(
                SqlCase::N()
                    ->when(new Q(array('event_id' => $event('assigned'))), 1)
            ),
            'Overdue' => SqlAggregate::COUNT(
                SqlCase::N()
                    ->when(new Q(array('event_id' => $event('overdue'))), 1)
            ),
            'Closed' => SqlAggregate::COUNT(
                SqlCase::N()
                    ->when(new Q(array('event_id' => $event('closed'))), 1)
            ),
            'Reopened' => SqlAggregate::COUNT(
                SqlCase::N()
                    ->when(new Q(array('event_id' => $event('reopened'))), 1)
            ),
            'Deleted' => SqlAggregate::COUNT(
                SqlCase::N()
                    ->when(new Q(array('event_id' => $event('deleted'))), 1)
            ),
        ));

        switch ($group) {
            case 'topic':
                $headers = array(__('Help Topic'));
                $header = function($row) { return Topic::getLocalNameById($row['topic_id'], $row['topic__topic']); };
                $pk = 'topic_id';
                $topics = Topic::getHelpTopics(false, Topic::DISPLAY_DISABLED);
                $stats = $stats
                    ->values('topic_id', 'topic__topic', 'topic__flags')
                    ->filter(array('topic_id__gt' => 0, 'topic_id__in' => array_keys($topics)))
                    ->distinct('topic_id');
                $times = $times
                    ->values('topic_id')
                    ->filter(array('topic_id__gt' => 0))
                    ->distinct('topic_id');
                break;
            case 'staff':
                $headers = array(__('Agent'));
                $header = function($row) { return new AgentsName(array('first' => $row['staff__firstname'], 'last' => $row['staff__lastname'])); };
                $pk = 'staff_id';
                $staff = Staff::getStaffMembers();
                $stats = $stats
                    ->values('staff_id', 'staff__firstname', 'staff__lastname')
                    ->filter(array('staff_id__in' => array_keys($staff)))
                    ->distinct('staff_id');
                $times = $times->values('staff_id')->distinct('staff_id');
                $depts = $thisstaff->getManagedDepartments();
                
                if ($thisstaff->hasPerm(ReportModel::PERM_AGENTS))
                    $depts = array_merge($depts, $thisstaff->getDepts());

                $Q = Q::any(array(
                    'staff_id' => $thisstaff->getId(),
                ));

                if ($depts) {
                    $Q->add(array('dept_id__in' => $depts));
                    $stats = $stats->filter(array('staff_id__gt'=>0))->filter($Q);
                    $times = $times->filter(array('staff_id__gt'=>0))->filter($Q);
                }
            break;
        case 'staff_ht':
            $HelpTopicName = '';
            $HelpTopicID = $_REQUEST['ht_id'];
            $GetHT_Q = "SELECT `topic` FROM `ost_help_topic` WHERE `topic_id` LIKE '$HelpTopicID';";

            if (($GetHT_Res = db_query($GetHT_Q)) && db_num_rows($GetHT_Res)) {
                $Res = db_fetch_row($GetHT_Res);
                
                if (isset($Res) && isset($Res[0]) && $Res[0] !== '') {
                    $HelpTopicName = $Res[0];
                }
            }

            $headers = array(__("Help Topic ($HelpTopicName) - Agents"));
            $header = function($row) { return new AgentsName(array('first' => $row['staff__firstname'], 'last' => $row['staff__lastname'])); };
            $pk = 'staff_id';
            $staff = Staff::getStaffMembers();
            $stats = $stats
                ->values('staff_id', 'staff__firstname', 'staff__lastname')
                ->filter(array('staff_id__in' => array_keys($staff)))
                ->distinct('staff_id');
            $times = $times->values('staff_id')->distinct('staff_id');

            $IsAgentNotAdminOnReports = !$thisstaff->isreportsadmin;

            if ($IsAgentNotAdminOnReports) {
                $depts = $thisstaff->getManagedDepartments();
            
                if ($thisstaff->hasPerm(ReportModel::PERM_AGENTS))
                    $depts = array_merge($depts, $thisstaff->getDepts());

                $Q = Q::any(array(
                    'staff_id' => $thisstaff->getId(),
                ));

                if ($depts) {
                    $Q->add(array('dept_id__in' => $depts));
                    $stats = $stats->filter(array('staff_id__gt'=>0))->filter($Q);
                    $times = $times->filter(array('staff_id__gt'=>0))->filter($Q);
                }
            }
            break;
        default:
            # XXX: Die if $group not in $groups
        }

        $timings = array();

        foreach ($times as $T) {
            $timings[$T[$pk]] = $T;
        }

        $rows = array();
        
        foreach ($stats as $R) {
            if (isset($R['dept__flags'])) {
                if ($R['dept__flags'] & Dept::FLAG_ARCHIVED)
                $status = ' - '.__('Archived');
                elseif ($R['dept__flags'] & Dept::FLAG_ACTIVE)
                $status = '';
                else
                $status = ' - '.__('Disabled');
            }
            
            if (isset($R['topic__flags'])) {
                if ($R['topic__flags'] & Topic::FLAG_ARCHIVED)
                $status = ' - '.__('Archived');
                elseif ($R['topic__flags'] & Topic::FLAG_ACTIVE)
                $status = '';
                else
                $status = ' - '.__('Disabled');
            }

            $T = $timings[$R[$pk]];
            $rows[] = array($header($R) . $status, $R['Opened'], $R['Assigned'],
                $R['Overdue'], $R['Closed'], $R['Reopened'], $R['Deleted'],
                number_format($T['ServiceTime'], 1),
                number_format($T['ResponseTime'], 1));
        }

        return array("columns" => array_merge($headers,
                        array(__('Opened'),__('Assigned'),__('Overdue'),__('Closed'),__('Reopened'),
                              __('Deleted'),__('Service Time'),__('Response Time'))),
                     "data" => $rows);
    }
}
