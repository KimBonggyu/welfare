<?php
include "_conf.php";

if($mode=="insert_productApplies"){
    $success = 0;

    $checkId_sql = "SELECT * FROM `new_tamburins`.`g5_member` WHERE mb_id = '".$tamburinsId."'";
    $checkId_res = sql_fetch($checkId_sql,null,$Conn7);
    if(!$checkId_res){
        alert('탬버린즈 회원가입 후 이용해주세요.');
    }

    $tamId = $checkId_res['mb_no'];

    $member_sql = "insert ProductApplies_TB set
        PGM_IDX='".$pgm_idx."'
        ,NOTE='".$note."'
        ,DELI_YN='1'
        ,ZIPCODE='".$zipcode."'
        ,ADDRESS='".$address."'
        ,ADDRESS_SUB='".$addressSub."'
        ,DELI_NAME='".$deliName."'
        ,PHONE='".$phone."'
        ,MB_NO='".$tamId."'
        ,`GROUP`='".$group."' 
        ,PAYMENT_CMPL='0' 
        ,MODDATE=NOW()
        ,REGDATE=now()
        ";
    $member_res = sql_query($member_sql, null, $Conn4);
    if(!$member_res){
        alert('Member Database error');
        return;
    }
    $pa_idx = sql_insert_id($Conn4);

    for($i=0; $i<count($mp_idx); $i++){

        $pcheck_sql = "SELECT * FROM `Master_Data`.`Master_Product_copy` WHERE MP_IDX = '".$mp_idx[$i]."'";
        $pcheck_data = sql_fetch($pcheck_sql,null,$Conn7);

        $product_sql = "insert ProductItems_TB set
             PA_IDX='".$pa_idx."'
            ,PCODE='".$pcheck_data['P_CODE']."'
            ,QTY='".$final_qty[$i]."'
            ,PRICE='".(($pcheck_data['PRICE']*$final_qty[$i])/2)."'
            ,MODDATE=NOW()
            ,REGDATE=NOW()
            ";
        if(sql_query($product_sql,null,$Conn4)){
            $success++;
        }else{
            alert('Insert Product data error');
        }
    }
    alert($success."건 신청성공하였습니다.");
}

if($mode=="load_list"){
    $searchSql = array();

    if($sdate!=""&&$edate!=""){
        $searchSql[] = " A.REGDATE >= '$sdate 00:00:00' and A.REGDATE <= '$edate 23:59:59' ";
    }
    if ($keyword != "") {
        if($group=="parttimer"){
            if($column=="UserName"){
                $column="NAME";
            }
        }
        $searchSql[] = $column . " like '%" . $keyword . "%' ";
    }
    if($flag!=""){
        $searchSql[] = " A.PAYMENT_CMPL='$flag' ";
    }else{
        $searchSql[] = " A.PAYMENT_CMPL !='4' ";
    }
    if($group!=""){
        $searchSql[] = " `GROUP`='$group' ";
    }
    if ($searchSql) {
        $search_res = " where " . @implode(" and ", $searchSql);
    }else{
        $search_res = " where A.PAYMENT_CMPL !=4 ";
    }
    $num_per_page = 10;
    $page_per_block = 10;

    if($group!="parttimer"){
        $sql = "SELECT * FROM `gm_intranet`.ProductApplies_TB AS A
                LEFT JOIN `global`.`GLOBAL_MEMBER` AS M
                USING(PGM_IDX)
                $search_res";

    }else{
        $sql = "SELECT * FROM `gm_intranet`.`ProductApplies_TB` AS A
                LEFT JOIN `sale_pos`.`PART_TIMER` AS M
                ON A.PGM_IDX = M.PT_IDX
                $search_res";
    }

    $res = sql_query($sql, null, $Conn4)or die(sql_error_info($Conn6));

    $cntPerPage = $rows; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " ORDER BY PA_IDX desc limit $this_start_num,$cntPerPage ";

    $res = sql_query($sql, null, $Conn4)or die(sql_error_info($Conn6));
    $html ="";
    $subhtml ="";

    if (sql_num_rows($res) != 0) {
        $j=0;
        while ($data = sql_fetch_assoc($res)) {
            $mpcode_arr=$mpname_arr=$pcode_arr=$qty_arr=$flag_arr=$pcode_str=array();

            $c=0;
            $bbsql = "SELECT PCODE, QTY, FLAG FROM `gm_intranet`.ProductItems_TB WHERE PA_IDX =".$data['PA_IDX']." ORDER BY PCODE DESC";
            $bbres = sql_query($bbsql, null, $Conn4);
            while($row=sql_fetch_assoc($bbres)){
                $pcode_arr[$c]=$row["PCODE"];
                $qty_arr[$c]=$row["QTY"];
                $flag_arr[$c]=$row["FLAG"];
                $c++;
            }
            $c=0;
            unset($pcode_str);
            $pcode_str="";
            $pcode_str=implode(',', $pcode_arr);

            $msql="SELECT PRODUCT_DESCRIPTION_SHORT, P_CODE FROM `Master_Data`.Master_Product_copy WHERE P_CODE IN (".$pcode_str.") ORDER BY P_CODE DESC";
            $mres=sql_query($msql,null,$Conn7);
            while($row=sql_fetch_assoc($mres)){
                $mpcode_arr[$c]=$row["P_CODE"];
                $mpname_arr[$c]=$row["PRODUCT_DESCRIPTION_SHORT"];
                $c++;
            }

            $html.=" <tr class='' style='height:130px;'>";
            $html.="<td style='text-align: center;' onclick='event.cancelBubble=true;'>
                    <input type='checkbox' id='chk_$j' name='check_idx[]' class='check_idx'
                    value='".$data['PA_IDX']."' title='내역선택' onclick='btn_onoff();' flag='".$data['PAYMENT_CMPL']."'></td>";
            $html.="<td class='text-center'>".$data['PA_IDX']."</td>";
            $html.="<td class='text-center'>".date("Y-m-d H:i", strtotime($data['REGDATE']))."</td>";
            $html.="<td class='text-center'>".($data['GROUP']=="member"?$data['GROUP_NAME']:"계약직 직원")."</td>";
            $html.="<td class='text-center'>".($data['GROUP']=="member"?$data['UserName']."(".$data['UserId'].")":$data['NAME'])."</td>";
            $data['PAYMENT_CMPL'] = $tam_flag[$data['PAYMENT_CMPL']];
            $html.="<td class='text-center'>".$data['PAYMENT_CMPL']."</td>";

            $html.="<td class='text-center'>";

            for($i=0; $i<count($mpname_arr); $i++){
                $subhtml .=  $mpname_arr[$i];
                if($flag_arr[$i]==1){
                    $subhtml .= " <span class='notice2'>(승인)</span>";
                }else if($flag_arr[$i]==8){
                    $subhtml .= " <span class='notice'>(반려)</span>";
                }
                $subhtml .= "</br>";
            }
            $html.= $subhtml."</td>";
            $subhtml ="";

            $bbres = sql_query($bbsql, null, $Conn4);
            $html.="<td class='text-center'>";
            for($i=0; $i<count($mpcode_arr); $i++){
                $subhtml .=  $mpcode_arr[$i]."<br/>";
            }
            $html.= $subhtml."</td>";
            $subhtml ="";

            $bbres = sql_query($bbsql, null, $Conn4);
            $html.="<td class='text-center'>";
            for($i=0; $i<count($qty_arr); $i++){
                $subhtml .=  $qty_arr[$i]."<br/>";
            }
            $html.= $subhtml."</td>";
            $subhtml ="";

            $fsql = "SELECT DISTINCT PA_IDX, EXPORT_CODE FROM ProductItems_TB WHERE PA_IDX = '".$data['PA_IDX']."'";
            $fres = sql_query($fsql, null, $Conn4);
            $fdata = sql_fetch_assoc($fres);
            $html.="<td class='text-center'>".($fdata['EXPORT_CODE']=="0"?"" : $fdata['EXPORT_CODE'])."</td>";
            $html.="<td class='text-center'><button type='button' class='btn btn-blank btn-sm order-detail-btn' onclick='open_detail(".$data['PA_IDX'].")'";
            if($data["PAYMENT_CMPL"]=="구매신청" || $data["PAYMENT_CMPL"]=="구매승인(결제대기)" || $data["PAYMENT_CMPL"]=="결제진행중" || $data["PAYMENT_CMPL"]=="구매반려"){
                $html.=">VIEW</button></td>";
            }else{
                $html.="disabled >VIEW</button></td>";
            }
            $j++;
            unset($mpcode_arr, $mpname_arr, $pcode_arr, $qty_arr, $flag_arr, $pcode_str);
        }
        $msg["rows"] = $html;
    } else {
        $html.="<tr><td colspan='18'>No data available</td></tr>";
        $msg["rows"] = $html;
    }
    //--------------------------------------페이징---------------------------
    $p_pageNo = $pageno;
    $p_pageLength = $cntPerPage;
    $p_recordCnt = $recordCnt;

    $startPageNo = (ceil($p_pageNo / $PAGE_PER_BLOCK) - 1) * $PAGE_PER_BLOCK + 1;
    $PrevPageNo = $p_pageNo - 1;
    $NextPageNo = $p_pageNo + 1;
    $PrevBlockNo = $startPageNo - $PAGE_PER_BLOCK;
    $NextBlockNo = $startPageNo + $PAGE_PER_BLOCK;

    if ($p_recordCnt > 0)
        $TotalPage = ceil($p_recordCnt / $p_pageLength);
    else
        $TotalPage = 0;

    $BlockCount = @ceil($TotalPage / $PAGE_PER_BLOCK);
    $lastBlockStartNum = $BlockCount * $PAGE_PER_BLOCK - $PAGE_PER_BLOCK + 1;

    $pageHtml = "<ul class=\"pagination pagination-sm\">";

    if ($PAGE_PER_BLOCK < $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $PrevBlockNo . "')\">&#5176;</li>";
    }

    for ($i = $startPageNo; $i < ($startPageNo + $PAGE_PER_BLOCK) && $i <= $TotalPage; $i++) {
        if ($i == $p_pageNo)
            $pageHtml .= "<li class='disable active'>" . $i . "</li>";
        else
            $pageHtml .= "<li onclick='paging(\"" . $i . "\")'>" . $i . "</li>";
    }
    if ($lastBlockStartNum > $p_pageNo) {
        $pageHtml .= "<li onclick=\"paging('" . $NextBlockNo . "')\">&#5171;</li>";
    }

    $pageHtml .= "</ul>";
    $msg["paging"] = $pageHtml;


    echo json_encode($msg);
}

if($mode=="load_member"){

    $data_rows = array();
    $searchSql = array();

    $searchSql[] = " RcmmndrRank NOT IN ('-', '', '#N/A') ";
    $searchSql[] = " UserId LIKE 'Monster%' ";

    if (!$retireFlag) {
        $retireFlag = "재직자";
    }
    if ($retireFlag == "재직자") {
        $searchSql[] = " RetireDate >= NOW() ";
        $subsql = " ,DATE_FORMAT(now(), '%Y')-DATE_FORMAT(JOIN_DATE, '%Y')+1 AS years ";
    } else {
        $searchSql[] = " RetireDate < NOW() ";
        $subsql = " ,DATE_FORMAT(RetireDate, '%Y')-DATE_FORMAT(JOIN_DATE, '%Y')+1 AS years ";
    }

    if ($keyword != "") {
        $searchSql[] = " (UserName like '%" . $keyword . "%' or UserId like '%" . $keyword . "%' ) ";
    }

    if ($searchSql) {
        $search_res = " where " . @implode(" and ", $searchSql);
    }

    $num_per_page = 10;
    $page_per_block = 10;

    $sql = " SELECT * $subsql
FROM global.GLOBAL_MEMBER
$search_res ";

    $res = sql_query($sql, null, $Conn2);
//    $msg =$product_sql;
//    echo json_encode($msg);
//    return;

    $cntPerPage = 10; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;
    $sql .= " ORDER BY CAST(SUBSTRING(UserId, 8) AS UNSIGNED) limit $this_start_num,$cntPerPage ";

    $res = sql_query($sql, null, $Conn2);
    if(sql_num_rows($res)!=0) {
        while ($r = sql_fetch_assoc($res)) {
            array_push($data_rows, $r);
        }
        $msg["rows"] = $data_rows;
        $msg["msg"]="OK";
    }else{
        $msg["msg"]="NO DATA";
    }

    echo json_encode($msg);
}

if($mode=="load_product"){
    $data_rows = array();

    if($keyword!=""){
        $keyword = strtoupper($keyword);
        $keywordsql = " AND (PRODUCT_DESCRIPTION_SHORT LIKE '%$keyword%' OR P_CODE LIKE '%$keyword%' ) ";
    }

    $checksql = "SELECT * FROM `gm_intranet`.AvailableProduct_TB ORDER BY AP_IDX DESC";
    $checkres = sql_query($checksql, null, $Conn4);
    $checkdata = array();
    while($row = sql_fetch_assoc($checkres)){
        $checkdata[] = $row["PCODE"];
    }
    $order_pcode = implode(',', $checkdata);

    $product_sql = "SELECT * FROM `Master_Data`.Master_Product_copy WHERE P_CODE IN (".$order_pcode.") ".$keywordsql." ORDER BY MP_IDX DESC";
//    $product_sql = "SELECT * FROM `Master_Data`.Master_Product_copy ".$keywordsql." ORDER BY MP_IDX";
    $product_res = sql_query($product_sql, null, $Conn7);


    $cntPerPage = 10; // 한페이지 출력개수
    $PAGE_PER_BLOCK = 10;
    $recordCnt = $recordCnt2 = $total_c = sql_num_rows($product_res);

    $totalpage = ceil($total_c / $cntPerPage);
    if ($totalpage < 1)
        $totalpage = 1;
    $this_start_num = $pageno * $cntPerPage - $cntPerPage;

    $psql = $product_sql;
    $next_start_num = $this_start_num+10;
    $psql .= " limit $next_start_num,$cntPerPage";
    $pres = sql_query($psql, null, $Conn7);
    if(sql_num_rows($pres)){
    }else{
        $msg["paging"]="no_next";
    }
    if($pageno==1){
        $msg["paging"]="no_prev";
    }

    $product_sql.=" limit $this_start_num,$cntPerPage ";
    $product_res = sql_query($product_sql, null, $Conn7);
    if(sql_num_rows($product_res)!=0) {
        while ($r = sql_fetch_assoc($product_res)) {
            array_push($data_rows, $r);
        }
        $msg["rows"] = $data_rows;
        $msg["msg"]="OK";
    }else{
        $msg["msg"]="NO DATA";
    }

    echo json_encode($msg);
}


if ($mode == "update_status_arr") {
    $success = 0 ;
    $fail = 0;
    $check = 0;

    if($flag==null){
        $flag=0;
    }

    $sql="UPDATE `gm_intranet`.ProductApplies_TB AS PA
         INNER JOIN `gm_intranet`.ProductItems_TB AS `PI` USING(PA_IDX)
         SET PA.PAYMENT_CMPL=".$flag.", `PI`.`COMMENT`=null, `PI`.FLAG=".$flag."
         WHERE PA.PA_IDX=";
    if($flag==1){
        for ($i = 0; $i < count($arr_item); $i++) {
            $check_sql = "SELECT PI_IDX FROM `gm_intranet`.ProductItems_TB AS `PI`
                        INNER JOIN `gm_intranet`.ProductApplies_TB AS PA USING(PA_IDX)
                        WHERE PA_IDX=".$arr_item[$i]." AND FLAG=8";

            $check_res=sql_query($check_sql,null,$Conn4);
            if(sql_num_rows($check_res)>0){
                $check++;
            }else{
            }
        }
        if($check){
            echo "선택한 항목 중 부분 승인/반려 처리된 품목이 있습니다.";
            exit;
        }
    }
    for ($i = 0; $i < count($arr_item); $i++) {

        $sql.=$arr_item[$i];

        if(sql_query($sql,null,$Conn4)){
            if($flag==1){
                //승인문자전송
                $phone_sql = "select GM.PHONE from `gm_intranet`.ProductApplies_TB AS PA left join `global`.GLOBAL_MEMBER as GM using(PGM_IDX) where PA_IDX='".$arr_item[$i]."'";
                $pdata = sql_fetch($phone_sql, null, $Conn4);
//                send_msg($pdata['PHONE']);
            }
            $success++;
        }else{
            $fail++;
        }
    }
    if($fail){
        echo "선택항목 결재 처리 중 오류가 발생했습니다. IT기획파트에 문의 바랍니다.";
    }else{
        echo $success."건 성공하였습니다.";
    }
}
if($mode=="update_export_code_arr"){

    $success = 0 ;
    for ($i = 0; $i < count($arr_item); $i++) {
        $sql = "update ProductItems_TB set EXPORT_CODE='".$export_code."' where PA_IDX='" . $arr_item[$i] . "' ";
        if(sql_query($sql,null,$Conn4)){
            $success++;
        }
    }
    echo $success."건 성공하였습니다.";
}

if($mode=="update_comment_arr"){
    $check=0;
    $success = 0 ;
    $fail=0;
    $pass=0;

    /* index.php에서 주문 자체에 대한 반려일 경우 */
    if($process=='group'){
        /* 부분 승인이 있는지 확인 후 진행 */
        for ($i = 0; $i < count($arr_item); $i++) {
            $check_sql = "SELECT PI_IDX FROM `gm_intranet`.ProductItems_TB AS `PI`
                        INNER JOIN `gm_intranet`.ProductApplies_TB AS PA USING(PA_IDX)
                        WHERE PA_IDX=".$arr_item[$i]." AND FLAG=1";

            $check_res=sql_query($check_sql,null,$Conn4);
            if(sql_num_rows($check_res)>0){
                $check++;
            }else{
            }
        }
        if($check){
            $msg["type"]='group';
            $msg["check"]=$check;
            echo json_encode($msg);
            exit;
        }else{
            $sql="UPDATE `gm_intranet`.ProductApplies_TB AS PA
                 INNER JOIN `gm_intranet`.ProductItems_TB AS `PI` USING(PA_IDX)
                SET PA.PAYMENT_CMPL=8, `PI`.`COMMENT`='".$comment."', `PI`.FLAG=8
                WHERE PA.PA_IDX=";
        }

        for ($i = 0; $i < count($arr_item); $i++) {
            $sql.=$arr_item[$i];

            if (sql_query($sql, null, $Conn4)) {
                $success++;
            }else{
                $fail++;
            }
        }
        $msg["type"]='group';

    /* order_datil.php에서 반려버튼 클릭에 대한 반려일 경우 */
    }else if($process=='unit'){
        $sql = "UPDATE `gm_intranet`.ProductItems_TB SET FLAG=".$flag.", COMMENT='".$comment."' WHERE PI_IDX=".$pi_idx;
        if(sql_query($sql,null,$Conn4)){
            $success++;
        }else{
            $fail++;
        }
        $msg["type"]='unit';

    /* order_detail.php에서 체크박스로 여러 제품에 대한 반려일 경우 */
    }else{
        $sql = "UPDATE `gm_intranet`.ProductItems_TB SET FLAG=".$flag.", COMMENT='".$comment."' WHERE PI_IDX IN (".$pi_idx.")";
        if(sql_query($sql,null,$Conn4)){
            $success=count(explode(',', $pi_idx));
        }else{
            $fail++;
        }
        $msg["type"]='unit_arr';
    }

    /* 부분 반려 후 해당 신청의 제품중 flag 확인 후 PAYMENT_CMPL 업데이트 */
    if($process=='unit' || $process=='unit_arr'){
        $check_sql = "SELECT DISTINCT PA_IDX, FLAG FROM `gm_intranet`.ProductItems_TB AS `PI`
                      WHERE PA_IDX=(
	                    SELECT DISTINCT PA_IDX FROM `gm_intranet`.ProductItems_TB AS `PI`
	                    WHERE PI_IDX IN (".$pi_idx.")
                      )";
        $check_res=sql_query($check_sql,null,$Conn4);
        while($row=sql_fetch_assoc($check_res)){
            $paidx=$row["PA_IDX"];
            if($row["FLAG"]!=0){
                if($row["FLAG"]==1){
                    $pass++;
                }
            }else{
                $check++;
            }
        }
        if($check){
        }else{
            if($pass){
                $f_sql="UPDATE `gm_intranet`.ProductApplies_TB SET PAYMENT_CMPL=1 WHERE PA_IDX=".$paidx;
            }else{
                $f_sql="UPDATE `gm_intranet`.ProductApplies_TB SET PAYMENT_CMPL=".$flag." WHERE PA_IDX=".$paidx;
            }
            $f_res=sql_query($f_sql,null,$Conn4);
            if($f_res){
                $msg["final"]="OK";
            }
        }
    }

    $msg["fail"]=$fail;
    $msg["success"]=$success;
    echo json_encode($msg);
}
if($mode=="update_order_arr"){
    $num = count($piidx_arr);
    $piidx=implode(",", $piidx_arr);
    $fail=0;
    $check_sql="";
    $msg["final"]="NO";

    if($flag==null){
        $flag=0;
    }
    /* 부분 승인 후 해당 신청의 제품중 flag 확인 후 PAYMENT_CMPL 업데이트 */
    if($flag=='1'){
        $sql = "UPDATE `gm_intranet`.ProductItems_TB SET FLAG=".$flag." WHERE PI_IDX IN (".$piidx.")";
        if(sql_query($sql,null,$Conn4)){
            $check_sql="SELECT DISTINCT PA_IDX,FLAG FROM `gm_intranet`.ProductItems_TB
                        WHERE PA_IDX = (
	                        SELECT DISTINCT PA_IDX FROM `gm_intranet`.ProductItems_TB
	                        WHERE PI_IDX IN (".$piidx.")
                        )";
            $check_res=sql_query($check_sql,null,$Conn4);
            while($row=sql_fetch_assoc($check_res)){
                $paidx=$row["PA_IDX"];
                if($row["FLAG"]!=0){
                }else{
                    $check++;
                }
            }
            if($check){
                $msg["msg"]="OK";
            }else{
                $f_sql="UPDATE `gm_intranet`.ProductApplies_TB SET PAYMENT_CMPL=".$flag." WHERE PA_IDX=".$paidx;
                $f_res=sql_query($f_sql,null,$Conn4);
                if($f_res){
                    $msg["final"]="OK";
                    //승인문자전송
                    $phone_sql = "select GM.PHONE from `gm_intranet`.ProductApplies_TB AS PA left join `global`.GLOBAL_MEMBER as GM using(PGM_IDX) where PA_IDX='".$arr_item[$i]."'";
                    $pdata = sql_fetch($phone_sql, null, $Conn4);
//                    send_msg($pdata['PHONE']);
                }else{$fail++;}
            }
        }else{$fail++;}

    }else{
        $sql = "UPDATE `gm_intranet`.ProductItems_TB AS `PI`
                INNER JOIN `gm_intranet`.ProductApplies_TB AS PA USING(PA_IDX)
                SET PAYMENT_CMPL=".$flag.", FLAG=".$flag.", COMMENT=NULL WHERE PI_IDX IN (".$piidx.")";
        if(sql_query($sql,null,$Conn4)){
            $msg["final"]="OK";
        }else{$fail++;}
    }

    $msg["num"] = $num;
    if($fail){
        $msg["fail"]=$fail;
    }
    echo json_encode($msg);
}
if($mode=="viewProduct"){
    $sql = "SELECT * FROM `new_tamburins`.g5_shop_item_copy WHERE it_code = '".$barcode."'";
    $data = sql_fetch($sql, null, $Conn7);
//    echo $data['it_id'];
    if($data['it_id']){
        echo $data['it_id'];
    }else{
        echo 'fail';
    }
}
if($mode=="search_product"){

    $result=sql_query("SELECT * FROM `Master_Data`.Master_Product_copy WHERE PRODUCT_DESCRIPTION_SHORT LIKE '%".$val."%' LIMIT 10", null, $Conn7);
    while($row=sql_fetch_array($result)) {

        $name = $row['PRODUCT_DESCRIPTION_SHORT']." (".$row['P_CODE'].")";
        $idx = $row['MP_IDX'];

        echo "
            <div class='display_box' onclick='setDetail(".$idx.")'>
                $name
            </div>";
    }
}
if($mode=="set_detail"){
    $result=sql_query("SELECT * FROM `Master_Data`.Master_Product_copy WHERE MP_IDX = '$idx'", null, $Conn7);
    $data=sql_fetch_assoc($result);
    $arr = array("name"=>$data['PRODUCT_DESCRIPTION_SHORT'], "code"=>$data['P_CODE'],"p88code"=>$data['P_88CODE']);
    echo json_encode($arr);
}

if($mode=="insert_ap"){

    $checksql = "SELECT * FROM `gm_intranet`.AvailableProduct_TB WHERE PCODE = '".$add_pcode."'";
    $checkres = sql_query($checksql, null, $Conn4);
    if(sql_num_rows($checkres)){
        echo "overlap";
    } else {
        $bbsql = "SELECT * FROM `Master_Data`.Master_Product_copy WHERE P_CODE = '".$add_pcode."'";
        $bbdata = sql_fetch($bbsql, null, $Conn7);
        $sql = "insert AvailableProduct_TB set
            PCODE='".$bbdata["P_CODE"]."'
            ,P_88CODE='".$bbdata["P_88CODE"]."'
            ,P_NAME='".$bbdata["PRODUCT_DESCRIPTION_SHORT"]."'
            ,REGDATE=now()";
        if(sql_query($sql,null,$Conn4)){
            echo "OK";
        }else{
            echo "FALSE";
        }
    }
}

if($mode=="delete_ap"){
//    $sql = "delete from AvailableProduct where AP_IDX='".$idx."'";
    $sql = "delete from `gm_intranet`.AvailableProduct_TB WHERE AP_IDX = '".$idx."'";
    if(sql_query($sql,null,$Conn4)){
        echo "OK";
    }else{
        echo "FALSE";
    }
}
if($mode=="check_amount"){
    $checksql="SELECT * FROM `Master_Data`.`Master_Product_copy` WHERE MP_IDX = '".$mpidx."'";
    $checkdata = sql_fetch($checksql, null, $Conn7);

    $sql = "SELECT * FROM `gm_intranet`.AvailableProduct_TB WHERE PCODE = '".$checkdata['P_CODE']."'";
    $data = sql_fetch($sql, null, $Conn4);

    if($data){
        $msg['rows']=$checkdata;
        $msg["msg"]="OK";
    } else {
        $msg["msg"]="empty";
    }
    echo json_encode($msg);
}

if($mode=="check_IdAdr"){
    $stack_adr = 0;
    $stack_id = 0;
    $no_adr = array(
        '서울 마포구 어울마당로5길 41',
        '서울 마포구 독막로7길 40',
        '서울 마포구 독막로7길 23',
        '서울 마포구 어울마당로5길 17',
        '서울 마포구 독막로8길 49'
    );
    $result = array_search($adr, $no_adr);
    if($result>-1){
        $stack_adr++;
    } else {
    }

    $checkId_sql = "SELECT * FROM `new_tamburins`.`g5_member` WHERE mb_id = '".$id."'";
    $checkId_res = sql_fetch($checkId_sql,null,$Conn7);

    if($checkId_res){
    } else {
        $stack_id++;
    }
    if($stack_adr){
        echo "no adr";
    } else if($stack_id){
        echo "no id";
    } else {
        echo "ok";
    }
}

if($mode=="active_item"){
    $msql = "SELECT * FROM Master_Product_copy WHERE P_CODE = '".$p_code."'";
    $mdata = sql_fetch($msql, null, $Conn7);

    $sql = "insert AvailableProduct_TB set
            PCODE='".$mdata["P_CODE"]."'
            ,P_88CODE='".$mdata["P_88CODE"]."'
            ,P_NAME='".$mdata["PRODUCT_DESCRIPTION_SHORT"]."'
            ,REGDATE=now()";
    if(sql_query($sql,null,$Conn4)){
        echo "OK";
    }else{
        echo "FALSE";
    }
}

if($mode=="deactive_item"){
    $msql = "SELECT * FROM Master_Product_copy WHERE P_CODE = '".$p_code."'";
    $mdata = sql_fetch($msql, null, $Conn7);

    $checksql = "SELECT * FROM AvailableProduct_TB WHERE PCODE = '".$p_code."'";
    $checkres = sql_query($checksql, null, $Conn4);

    if(sql_num_rows($checkres) != 0){
        $checkdata = sql_fetch($checksql,null,$Conn4);
        $sql = "delete from AvailableProduct_TB where AP_IDX='".$checkdata['AP_IDX']."'";
        if(sql_query($sql,null,$Conn4)){
            echo "OK";
        }else{
            echo "FALSE";
        }
    }else{
        echo "ERROR";
    }
}

if($mode=="check_active_item"){
    $arr_idx= implode(',', $arr_item);
    $sql="SELECT * FROM `gm_intranet`.AvailableProduct_TB WHERE PCODE IN (".$arr_idx.")";
    $res=sql_query($sql,null,$Conn4);
    $num=sql_num_rows($res);

    if(($flag==1) && ($num>0)){
        echo "NO";
    }else if(($flag==2) && (count($arr_item)!=$num)){
        echo "NO";
    }else{
        echo "OK";
    }
}

if($mode=="activation_item_arr"){
    $success = 0 ;
    $arr_pcode= implode(',', $arr_item);
    $msql = "SELECT * FROM Master_Product_copy WHERE P_CODE IN (".$arr_pcode.")";
    $mres = sql_query($msql, null, $Conn7);
    while($mdata = sql_fetch_assoc($mres)){
        if($flag=='1'){
            $sql = "insert AvailableProduct_TB set
            PCODE='".$mdata["P_CODE"]."'
            ,P_88CODE='".$mdata["P_88CODE"]."'
            ,P_NAME='".$mdata["PRODUCT_DESCRIPTION_SHORT"]."'
            ,REGDATE=now()";

            if(sql_query($sql,null,$Conn4)){
                $success++;
            }
        }else{
            $sql = "delete from AvailableProduct_TB where PCODE='".$mdata['P_CODE']."'";

            if(sql_query($sql,null,$Conn4)){
                $success++;
            }
        }
    }
    echo $success."건 성공하였습니다.";
}

if($mode=="load_order_detail"){
    $member_html="";
    $order_html="";
    $j=0;
    $c=0;

    $order_sql="SELECT `PI`.PA_IDX, PI_IDX, PCODE, `COMMENT`, FLAG, QTY, PA.REGDATE, PGM_IDX FROM ProductApplies_TB AS PA
                INNER JOIN ProductItems_TB AS `PI` USING(PA_IDX)
                WHERE PA_IDX=".$paidx."
                ORDER BY PCODE DESC";
    $order_res=sql_query($order_sql,null,$Conn4);
    while($row=sql_fetch_assoc($order_res)){
        $piidx_arr[$c]=$row["PI_IDX"];
        $pcode_arr[$c]=$row["PCODE"];
        $c++;
    }
    $c=0;
    $pcode_str=implode(',', $pcode_arr);

    $msql="SELECT PRODUCT_DESCRIPTION_SHORT, P_CODE FROM `Master_Data`.Master_Product_copy WHERE P_CODE IN (".$pcode_str.") ORDER BY P_CODE DESC";
    $mres=sql_query($msql,null,$Conn7);
    while($row=sql_fetch_assoc($mres)){
        $mpcode_arr[$c]=$row["P_CODE"];
        $mpname_arr[$c]=$row["PRODUCT_DESCRIPTION_SHORT"];
        $c++;
    }
    $c=0;

    $order_res=sql_query($order_sql,null,$Conn4);
    $order_data=sql_fetch_assoc($order_res);

    $member_sql="SELECT * FROM `global`.GLOBAL_MEMBER WHERE PGM_IDX='".$order_data['PGM_IDX']."'";
    $member_res=sql_query($member_sql,null,$Conn4);
    $member_data=sql_fetch($member_sql,null,$Conn4);

    $member_html.="<div class='manage-info flex flex-between'><h5 id='username' class='user-name'>".$member_data['UserName']." (".$member_data['UserId'].")</h5>";
    $member_html.="<p class='user-info'>부서  :  ".$member_data['GROUP_NAME']."</p></div><hr>";

    $member_html.="<div class='manage-info flex flex-between' style='margin:30px 0 5px;'><div style='font-size:0;'>주문번호  :  ".$paidx."</div>";
    $member_html.="<div>신청일  :  ".date('Y-m-d H:i', strtotime($order_data['REGDATE']))."</div></div>";

    $order_res=sql_query($order_sql,null,$Conn4);
    $i=0;
    while($order_data=sql_fetch_assoc($order_res)){
        $order_html.="<tr style='text-align:center'>";
        $order_html.="<td onclick='event.cancelBubble=true;'>
                    <input type='checkbox' id='order_chk_".$order_data['PI_IDX']."' name='order_check_idx[]' class='order_check_idx' flag='".$order_data['FLAG']."'
                    onclick='order_btn_onoff();' value='".$order_data['PI_IDX']."' title='내역선택' onclick='btn_onoff();'></td>";

        $order_html.="<td id='order_pname_".$order_data['PI_IDX']."'>".$mpname_arr[$i];
        if($order_data["FLAG"]==8){
            $order_html.=" <span class='notice'>(".$order_data["COMMENT"].")</span>";
        }else if($order_data["FLAG"]==1){
            $order_html.=" <span class='notice2'>(승인)</span>";
        }
        $order_html.="</td>";
        $order_html.="<td>".$mpcode_arr[$i]."</td>";
        $order_html.="<td>".$order_data['QTY']."</td>";


        $order_html.="<td><button type='button' class='btn btn-sm btn-default' id='update_order_btn_".$order_data['PI_IDX']."' onclick='updateOrder(".$order_data['PI_IDX'].", \"8\")'";
        if($order_data['FLAG']==8 || $order_data['FLAG']==1){
            $order_html.="disabled>반려</button></td>";
        }else{
            $order_html.=">반려</button></td>";
        }
        $order_html.="</tr>";
        $i++;
        $j++;
    }

    $msg["member_rows"]=$member_html;
    $msg["order_rows"]=$order_html;

    echo json_encode($msg);
}

function send_msg($hp){

    $config['cf_icode_server_ip'] = '211.172.232.124';
    $config['cf_icode_server_port'] = '7295';
    $config['cf_icode_id'] = 'tamburins';
    $config['cf_icode_pw'] = 'flswm17@';
    $config['cf_sms_use'] = 'icode';

    include_once('/home/welfare_new/www/lib/icode.lms.lib.php');

    $send_hp_mb = "16441246"; // 보내는 전화번호
    $recv_hp_mb = $hp; //  받는 전화번호

    $sms_content = "
[tamburins]
복지가 승인되었습니다. 3일 이내 결제 부탁드립니다.
감사합니다.
		";

    $send_number = str_replace("-", "", $send_hp_mb); // - 제거
    $recv_number = str_replace("-", "", $recv_hp_mb); // - 제거

    $strDest[0] = $recv_number;
    $SMS = new LMS; // SMS 연결
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], '1');
    $SMS->Add($strDest, $send_number, $config['cf_icode_id'], "", "", iconv("utf-8", "euc-kr", stripslashes($sms_content)), "", "1");

    if ($SMS->Send()) {
        return "ok";
    } else {
        return "false";
    }
}

?>
