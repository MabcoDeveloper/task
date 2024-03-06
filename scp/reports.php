<?php
/*********************************************************************
    reports.php

    Staff's Reports - basic stats...etc.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');

if (!$thisstaff || !$thisstaff->isManager()) {
    // header("location:directory.php?error=access_denied");
}

require_once INCLUDE_DIR . 'class.reports.php';

if ($_POST['export']) {
    $report = new OverviewReport($_POST['start'], $_POST['period']);
    switch (true) {
    case ($data = $report->getTabularData($_POST['export'])):
        $ts = strftime('%Y%m%d');
        $group = Format::slugify($_POST['export']);
        $delimiter = ',';
        if (class_exists('NumberFormatter')) {
            $nf = NumberFormatter::create(Internationalization::getCurrentLocale(),
                NumberFormatter::DECIMAL);
            $s = $nf->getSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
            if ($s == ',')
                $delimiter = ';';
        }

        Http::download("stats-$group-$ts.csv", 'text/csv');
        $output = fopen('php://output', 'w');
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, $data['columns'], $delimiter);
        foreach ($data['data'] as $row)
            fputcsv($output, $row, $delimiter);
        exit;
    }
}

$nav->setTabActive('reports');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.dashboard" />', "$('#content').data('tipNamespace', 'dashboard.dashboard');");

require(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.'reports.inc.php');
include(STAFFINC_DIR.'footer.inc.php');

if (isset($_REQUEST['ht_id']) && $_REQUEST['ht_id'] !== '') {
    echo "<script>
        $('.clean.tabs li').removeClass('active');
        $('.clean.tabs li:nth-child(3)').show();
        setTimeout(function(){
            $('html, body').animate({
                scrollTop: $('#staff-ht').offset().top - 150
            }, 500);
        }, 500);
    </script>";
}
?>
