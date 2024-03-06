<?php
require_once "db.php";

//export table data to excel


$fromdate =   $_GET["txt_from_date"];
$todate = $_GET["txt_to_date"];
$date = $_GET["txt_Date"];

if (isset($_GET["btn_employeesPref_report"])) {
      if ($fromdate < $todate) {


            $output = "";

            $output .= '<table class="table table-bordered" border="1">  
                    <tr>  
                         <th scope="col">#</th>
                          <th scope="col">number</th>
                          <th scope="col">حالة التفعيل</th>
                          <th scope="col">الصالة</th>
                          <th scope="col">الالية</th>
                          <th scope="col">اسم السائق</th>
                          <th scope="col">المحافظة</th>
                          <th scope="col">اسم الموظف</th>
                          <th scope="col">التاريخ</th>
                    </tr>';



            $sql = 'SELECT t.team_id, e.status ,
   TRIM(TRAILING "<br /> " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(en1.body,"نوع المركبة : ",-1),"وقت الإرسال : ",1)) vehicle_type,
   TRIM(TRAILING "<br /> "  FROM SUBSTRING_INDEX(SUBSTRING_INDEX(en1.body," المدينة :",-1),"من قِبل : ",1)) City,
   TRIM(TRAILING "<br /> "  FROM SUBSTRING_INDEX(SUBSTRING_INDEX(en1.body,"اسم السائق:",-1),"رقم الموبايل : ",1)) Driver_name,
   u.name, t.number, t.created,s.username
   from ost_ticket t INNER join ost_thread th on th.object_id =t.ticket_id inner join (select thread_id ,body from ost_thread_entry  where user_id= 250)  en1 on en1.thread_id = th.id 
   inner join (select thread_id ,staff_id from ost_thread_entry  where user_id= 0)  en2 on en2.thread_id = th.id 
   inner join ost_staff  s on s.staff_id=en2.staff_id
   left join ost_reservation r on r.ticket_id=t.ticket_id
   left join ost_user u on u.id =r.user_id
   INNER join (select max(id) id , ticket_id , max(STATUS_id) STATUS_id , max(staff_id)staff_id ,MAX(user_id)user_id , max(created)created from ost_connect_status  group by ticket_id having id = max(id)) c on t.ticket_id=c.ticket_id
   INNER join ost_external_status e on  c.status_id=e.id
   WHERE t.team_id=154 and t.created >= "' . ($fromdate) . '" and t.created <= "' . ($todate) . '"
   group by u.name, t.number, t.created,s.username,en1.body  
   ORDER BY `t`.`created`  ASC';
            $stmt = $con->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as $key => $value) {

                  $output .= '<tr>  
                         <td>' . ($key + 1) . '</td>   
                         <td>' . $value['number'] . '</td> 
                         <td>' . $value['status'] . '</td> 
                         <td>' . $value['name'] . '</td>  
                         <td>' . $value['vehicle_type'] . '</td>   
                         <td>' . $value['Driver_name'] . '</td>   
                         <td>' . $value['City'] . '</td>   
                         <td>' . $value['username'] . '</td>   
                         <td>' . $value['created'] . '</td>   
                          
                    </tr>';
            }

            $output .= '</table>';
            if (count($data) > 0) {
                  $filename = "Employees_Performance_" . date('Ymd') . ".xls";
                  header('Content-Encoding: UTF-8');
                  header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
                  header("Content-Disposition: attachment; filename=\"$filename\"");
                  header("Pragma: no-cache");
                  header("Expires: 0");
            echo pack("CCC",0xef,0xbb,0xbf);

                  echo $output;
                  echo '<script>
      location.reload();
      </script>';
                  echo '<script>
     setTimeout(() => {  window.location.href = "/task/scp/tickets.php"; }, 2000); ;
      </script>';
                  exit();
            } else {
                  echo '<script>
      alert("there are no data at the selected period")
      history.back()
      </script>';
            }
      }
} else if (isset($_GET["btn_beeorder_stk"])) {
      $sql_Delete="DELETE from `ost_beeorder_stocks`";
     $result1 = mysqli_query($conn, $sql_Delete);
     $url = "http://showman2.mabcoonline.biz:8088/service1.svc/Get_Beeorder_stocks";
      $y = CallAPIURL($url)["Get_Beeorder_stocks_Result"];
      foreach ($y as $element) {
        $sql_Insert="INSERT INTO `ost_beeorder_stocks`(`stk_code`, `stk_desc`, `mobile_type`, `loc_code`, `loc_name`, `qty`)
         VALUES ('" . $element['stk_code'] . "','" . $element['stk_desc'] . "','" . $element['mobile_type'] . "','" . $element['loc_code'] . "','" . $element['loc_name'] . "','" . $element['qty'] . "')";
        $result1 = mysqli_query($conn, $sql_Insert);
      }
        $output = "";

        $output .= '<table class="table table-bordered" border="1">  
                <tr>  
                      <th scope="col">المحافظة</th>
                      <th scope="col">عدد الطلبات المتاحة</th>
                      <th scope="col">عدد الطلبات لليوم السابق </th>
                      <th scope="col">عدد المواعيد المحددة لليوم السابق </th>
                      <th scope="col">عدد الحقائب المتاحة للتفعيل</th>
                      <th scope="col">عدد الحقائب الحقيقي </th>
                </tr>';

      $sql="SELECT   notes province , LDtotqty.qty LastDayRes ,count(lrd.id) lastDayDoneRes ,totqty.qty qtyAv  ,FLOOR( (sum(b.qty)*1.7) - count(r.id)) QtyAvForRes,sum(b.qty) Totqty
      from ost_user u 
      LEFT join (select * from  ost_beeorder_stocks where stk_code = '6210104000019' and mobile_type ='SL')b  on b.loc_code = u.loc_code 
      inner join ost_user__cdata uc on uc.user_id = u.id
      left join (SELECT distinct  count(*) qty ,   city from ost_ticket t where `team_id` = 154 and city is not null 
and (t.ticket_id in
     (select ticket_id from ost_connect_status s where  status_id = 10 and ticket_id =t.ticket_id and id in (select max(id) from ost_connect_status where ticket_id = t.ticket_id) ) or ticket_id not in (select ticket_id from ost_connect_status))
     group by city 
) totqty on  totqty.`city` =  uc.notes 
      left join (SELECT count(*) qty   ,city from ost_ticket t
                 where date(t.created) =  DATE_SUB(CURDATE(),INTERVAL 1 DAY ) and `team_id` = 154 and city is not null group by city) LDtotqty on LDtotqty.city = uc.notes
      left join (SELECT r.*  FROM `ost_reservation` r inner join ost_connect_status c on c.ticket_id = r.ticket_id where c.status_id = 4 and  c.created > CURDATE()   AND `activation_date` IS NULL AND `reservation_type` LIKE 'D' group by user_id ) r on r.user_id = u.id
      left join (SELECT r.* FROM `ost_reservation` r   inner join ost_connect_status c on c.ticket_id = r.ticket_id where c.status_id = 4 and  date(`created`) =  DATE_SUB(CURDATE(),INTERVAL 1 DAY)
                 and r.ticket_id in (SELECT t.ticket_id FROM ost_ticket t 
                                  inner join ost_connect_status c on t.`ticket_id` = c.`ticket_id`
                                  inner join ost_external_status e on e.id = c.status_id where e.id = 4 )) lrd on lrd.user_id =u.id
   where notes is not null and notes<>''
      group by uc.notes ";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as $key => $value) {

              $output .= '<tr>  
                     <td>' . $value['province'] . '</td> 
                     <td>' . $value['qtyAv'] . '</td> 
                     <td>' . $value['LastDayRes'] . '</td>  
                     <td>' . $value['lastDayDoneRes'] . '</td>   
                     <td>' . $value['QtyAvForRes'] . '</td>   
                     <td>' . $value['Totqty'] . '</td>   
                </tr>';
        }

        $output .= '</table>';
        if (count($data) > 0) {
              $filename = "جرد مواد بي أوردر" . date('Ymd') . ".xls";
              header('Content-Encoding: UTF-8');
      header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
              header("Content-Disposition: attachment; filename=\"$filename\"");
              header("Pragma: no-cache");
              header("Expires: 0");
            echo pack("CCC",0xef,0xbb,0xbf);

              echo $output;
              echo '<script>
  location.reload();
  </script>';
              echo '<script>
 setTimeout(() => {  window.location.href = "/task/scp/tickets.php"; }, 2000); ;
  </script>';
              exit();
        } else {
              echo '<script>
  alert("there are no data at the selected period")
  history.back()
  </script>';
        }
      
}
else if (isset($_GET["btn_all_beeorder_stks"])) {
      $sql_Delete="DELETE from `ost_beeorder_stocks`";
      $result1 = mysqli_query($conn, $sql_Delete);
      $url = "http://showman2.mabcoonline.biz:8088/service1.svc/Get_Beeorder_stocks";
      $y = CallAPIURL($url)["Get_Beeorder_stocks_Result"];
       foreach ($y as $element) {
         $sql_Insert="INSERT INTO `ost_beeorder_stocks`(`stk_code`, `stk_desc`, `mobile_type`, `loc_code`, `loc_name`, `qty`)
          VALUES ('" . $element['stk_code'] . "','" . $element['stk_desc'] . "','" . $element['mobile_type'] . "','" . $element['loc_code'] . "','" . $element['loc_name'] . "','" . $element['qty'] . "')";
         $result1 = mysqli_query($conn, $sql_Insert);
       }
      $output = "";

      $output .= '<table class="table table-bordered" border="1">  
              <tr>  
                    <th scope="col">اسم الصالة</th>
                    <th scope="col">المدينة</th>
                    <th scope="col">كود المادة</th>
                    <th scope="col">اسم المادة </th>
                    <th scope="col">الكمية</th>
                    <th scope="col">الكمية التالفة</th>
              </tr>';

      $sql = "SELECT u.name , notes,stk_code , stk_desc ,mobile_type, (SELECT qty   FROM ost_beeorder_stocks  where mobile_type='sr' and loc_code= b.loc_code and stk_code =b.stk_code ) SR_qty ,(SELECT qty   FROM ost_beeorder_stocks  where mobile_type='sl' and loc_code= b.loc_code and stk_code =b.stk_code ) SL_qty FROM `ost_beeorder_stocks` b  inner join ost_user u on b.loc_Code = u.loc_code LEFT join ost_user__cdata uc on uc.user_id = u .id    GROUP BY stk_code,notes,`name` order by stk_code,notes,name";
      $stmt = $con->prepare($sql);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($data as $key => $value) {

            $output .= '<tr>  
                   <td>' . $value['name'] . '</td> 
                   <td>' . $value['notes'] . '</td> 
                   <td>' . $value['stk_code'] . '</td>  
                   <td>' . $value['stk_desc'] . '</td>   
                   <td>' . $value['SL_qty'] . '</td>   
                   <td>' . $value['SR_qty'] . '</td>   
              </tr>';
      }

      $output .= '</table>';
      if (count($data) > 0) {
            $filename = "جرد كافة مواد بي أوردر" . date('Ymd') . ".xls";
      header('Content-Encoding: UTF-8');
      header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo pack("CCC",0xef,0xbb,0xbf);

            echo $output;
            echo '<script>
location.reload();
</script>';
            echo '<script>
setTimeout(() => {  window.location.href = "/task/scp/tickets.php"; }, 2000); ;
</script>';
            exit();
      } else {
            echo '<script>
alert("there are no data at the selected period")
history.back()
</script>';
      }
    
}
else if (isset($_GET["btn_stk_on_hand"])) {
     $url= "http://showman2.mabcoonline.biz:8088/service1.svc/Get_Beeorder_stk_on_hand/".$date;
      $sql_Delete="DELETE from `ost_beeorder_stocks`";
      $result1 = mysqli_query($conn, $sql_Delete);
       $y =  CallAPIURL($url)["Get_Beeorder_stk_on_hand_Result"];
       
       foreach ($y as $element) {
          
         $sql_Insert="INSERT INTO `ost_beeorder_stocks`(`stk_code`, `stk_desc`, `mobile_type`, `loc_code`, `loc_name`, `qty`)
          VALUES ('" . $element['stk_code'] . "','" . $element['stk_desc'] . "','" . $element['mobile_type'] . "','" . $element['loc_code'] . "','" . $element['loc_name'] . "','" . $element['qty'] . "')";
         $result1 = mysqli_query($conn, $sql_Insert);
       }
       
      $output = "";

      $output .= '<table class="table table-bordered" border="1">  
              <tr>  
                    <th scope="col">اسم الصالة</th>
                    <th scope="col">المدينة</th>
                    <th scope="col">كود المادة</th>
                    <th scope="col">اسم المادة </th>
                    <th scope="col">الكمية</th>
                    <th scope="col">الكمية التالفة</th>
              </tr>';

      $sql = "SELECT u.name , notes,stk_code , stk_desc ,mobile_type, ifnull((SELECT qty   FROM ost_beeorder_stocks  where mobile_type='sr' and loc_code= b.loc_code and stk_code =b.stk_code group by stk_Code , loc_Code , mobile_type ),0) SR_qty ,ifnull((SELECT qty   FROM ost_beeorder_stocks  where mobile_type='sl' and loc_code= b.loc_code and stk_code =b.stk_code group by  stk_Code , loc_Code , mobile_type),0) SL_qty FROM `ost_beeorder_stocks` b  inner join ost_user u on b.loc_Code = u.loc_code LEFT join ost_user__cdata uc on uc.user_id = u .id    GROUP BY stk_code,notes,`name` order by stk_code,notes,name";
      $stmt = $con->prepare($sql);
      $stmt->execute();
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($data as $key => $value) {
           
            $output .= '<tr>  
                   <td>' . $value['name'] . '</td> 
                   <td>' . $value['notes'] . '</td> 
                   <td>' . $value['stk_code'] . '</td>  
                   <td>' . $value['stk_desc'] . '</td>   
                   <td>' . $value['SL_qty'] . '</td>   
                   <td>' . $value['SR_qty'] . '</td>   
              </tr>';
      }
  
      $output .= '</table>';
      if (count($data) > 0) {
            $filename = "جرد مواد بي أوردر بتاريخ" . $date . ".xls";
      header('Content-Encoding: UTF-8');
      header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo pack("CCC",0xef,0xbb,0xbf);

            echo $output;
            echo '<script>
location.reload();
</script>';
            echo '<script>
setTimeout(() => {  window.location.href = "/task/scp/tickets.php"; }, 2000); ;
</script>';
            exit();
      } else {
            echo '<script>
alert("there are no data at the selected period")
history.back()
</script>';
      }
    
}

else if (isset($_GET["btn_beeorder_stocks_actions_recive"]) ||isset($_GET["btn_beeorder_stocks_actions_send"])) {
      $type = "";
      $filename = "";
      if( isset($_GET["btn_beeorder_stocks_actions_recive"]))
      {
      $type = "R";
      $filename = "حركة المواد الداخلة". date('Ymd') . ".xls";}
      else 
      {
      $type = "I";
      $filename = "حركة المواد المخرجة". date('Ymd') . ".xls";}
      if ($fromdate < $todate) {

      $url= "http://showman2.mabcoonline.biz:8088/service1.svc/Get_beeorder_stocks_actions/".$type.",".$fromdate.",".$todate."";
      
        $y =  CallAPIURL($url)["Get_beeorder_stocks_actions_Result"];
     

       
       $output = "";
 
       $output .= '<table class="table table-bordered" border="1">  
               <tr>  
                     <th scope="col">رمز المادة </th>
                     <th scope="col">اسم المادة</th>
                     <th scope="col">الكمية</th>
                     <th scope="col">اسم السائق </th>
                     <th scope="col">رقم السائق</th>
                     <th scope="col">المحافظة</th>
                     <th scope="col">الصالة</th>
                     <th scope="col">التاريخ والوقت</th>
                     <th scope="col">الرمز</th>
                  
               </tr>';
 
     
 
       foreach ($y as $element) {
          
            $SQLQueue = "select  u.name,notes from ost_user u  LEFT join ost_user__cdata uc on uc.user_id = u .id   where loc_Code = '".$element['loc_Code'] ."'";
            $stmt = $con->prepare($SQLQueue);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($data as $key => $value) {
                  $name =   $value['name'] ;
                  $notes = $value['notes'] ;
            }
            $output .= '<tr>  
            <td>' . $element['STK_Code']. '</td> 
            <td>' . $element['stk_desc']. '</td> 
            <td>' .$element['qty'] . '</td>  
            <td>' . $element['custm_name'] . '</td>   
            <td>' . $element['phone1'] . '</td>   
            <td>' .  $name . '</td>   
            <td>' .  $notes . '</td>   
            <td>' . $element['trn_dt'] . '</td>   
            <td>' . $element['remarks'] . '</td>   
       </tr>';
           
          }
          
       $output .= '</table>';
       if (count($y) > 0) {
           
       header('Content-Encoding: UTF-8');
       header( "Content-type: application/vnd.ms-excel; charset=UTF-8" );
             header("Content-Disposition: attachment; filename=\"$filename\"");
             header("Pragma: no-cache");
             header("Expires: 0");
             echo pack("CCC",0xef,0xbb,0xbf);
 
             echo $output;
             echo '<script>
 location.reload();
 </script>';
             echo '<script>
 setTimeout(() => {  window.location.href = "/task/scp/tickets.php"; }, 2000); ;
 </script>';
             exit();
       } else {
//              echo '<script>
//  alert("there are no data at the selected period")
//  history.back()
//  </script>';
       }
   

}
 }

function CallAPIURL($url)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL =>  $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json",
            "cache-control: no-cache"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    $data = json_decode($response, true);
    return $data;
}