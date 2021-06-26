<?php
include "_conf.php";

if($mode=="insert_productApplies"){
//     include '_proc_kakao.php';
    $success = 0;

    $checkId_sql = "SELECT * FROM `new_tamburins`.`g5_member` WHERE mb_id = '".$tamburinsId."'";
    $checkId_res = sql_fetch($checkId_sql,null,$Conn7);

    $tamId = $checkId_res['mb_no'];

    $member_sql = "insert ProductApplies_TB set
        PGM_IDX='".$_SESSION['PGM_IDX']."'
        ,NOTE='".$note."'
        ,DELI_YN='1'
        ,ZIPCODE='".$zipcode."'
        ,ADDRESS='".$address."'
        ,ADDRESS_SUB='".$addressSub."'
        ,DELI_NAME='".$deliName."'
        ,PHONE='".$phone."'
        ,MB_NO='".$tamId."'
        ,`GROUP`='member' 
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

    $total_sum = 0;
    
    for($i=0; $i<count($mp_idx); $i++){
        $total_sum += $final_qty[$i];
        
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
        if($i==0){
            $item_name = $pcheck_data['PRODUCT_DESCRIPTION_KR'];
        }
        if(sql_query($product_sql,null,$Conn4)){
            $success++;
        }else{
            alert('Insert Product data error');
        }
    }
    if(count($mp_idx)>1){
        $item_name .= " 외 " . ($total_sum-1) ."건";
    }
    $orderArray = array(
        'order_id' => $pa_idx,
        'user_id' => $_SESSION['M_ID'],
        'order_prd_nm' => $item_name,
        'ord_row' => $total_sum,
        'total_price' => $total_amt,
    );
//     echo CallPaymentKakaoPay($orderArray);
    echo $item_name." 신청 성공하였습니다.";
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

if($mode=="viewProduct"){
    $sql = "SELECT * FROM `new_tamburins`.g5_shop_item_copy WHERE it_code = '".$barcode."'";
    $data = sql_fetch($sql, null, $Conn7);

    if($data["it_id"]){
        $msg["rows"]=$data["it_id"];
        $msg["msg"]="OK";
    }else{
        $msg["msg"]="NO";
    }
    echo json_encode($msg);
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
if($mode=="cancel_order"){
    $checksql="SELECT * FROM ProductApplies_TB WHERE PA_IDX = '".$paidx."'";
    $checkres=sql_query($checksql,null,$Conn4);
    if(sql_num_rows($checkres)){
        $sql="UPDATE ProductApplies_TB SET PAYMENT_CMPL=4 WHERE PA_IDX='".$paidx."'";
        $res=sql_query($sql,null,$Conn4);
        if($res){}else{echo "취소처리 중 오류가 발생했습니다. IT기획파트에 문의 바랍니다.";}
    }else{
        echo "취소처리 중 오류가 발생했습니다. IT기획파트에 문의 바랍니다.";
    }

    echo "정상적으로 취소 처리되었습니다.";
}

if($mode=="payment_order"){
    $checksql="SELECT * FROM ProductApplies_TB WHERE PA_IDX = '".$paidx."'";
    $checkres=sql_query($checksql,null,$Conn4);
    if(sql_num_rows($checkres)){
        $sql="UPDATE ProductApplies_TB SET PAYMENT_CMPL=3 WHERE PA_IDX='".$paidx."'";
        $res=sql_query($sql,null,$Conn4);
        if($res){
            echo "결제 정상처리";
        }else{
            echo "취소처리 중 오류가 발생했습니다. IT기획파트에 문의 바랍니다.";
        }
    }else{
        echo "취소처리 중 오류가 발생했습니다. IT기획파트에 문의 바랍니다.";
    }
}

if($mode=="load_applying"){
    $html="";
    $addr="";
    $status="";
    $total_price=0;

    $sql="SELECT PAYMENT_CMPL, `PI`.PA_IDX, PI_IDX, PCODE, FLAG, QTY, PRICE, ADDRESS, ADDRESS_SUB FROM ProductApplies_TB AS PA
         INNER JOIN ProductItems_TB AS `PI` USING(PA_IDX)
         WHERE PA_IDX=".$paidx."
         ORDER BY PCODE DESC";
    $res=sql_query($sql,null,$Conn4);
    while($data=sql_fetch_assoc($res)){
        if($data["PAYMENT_CMPL"]==0){
            $check_pcode_arr[]=$data["PCODE"];
            $qty_arr[]=$data["QTY"];
            $total_price+=$data["PRICE"];
        }else if($data["PAYMENT_CMPL"]==1){
            if($data["FLAG"]==1){
                $check_pcode_arr[]=$data["PCODE"];
                $qty_arr[]=$data["QTY"];
                $total_price+=intval($data["PRICE"]);
            }
        }
        $addr=$data["ADDRESS"]." ".$data["ADDRESS_SUB"];
        $status=$data["PAYMENT_CMPL"];
    }
    $pcode_str=implode(',', $check_pcode_arr);

    $msql="SELECT PRODUCT_DESCRIPTION_SHORT FROM `Master_Data`.Master_Product_copy WHERE P_CODE IN (".$pcode_str.") ORDER BY P_CODE DESC";
    $mres=sql_query($msql,null,$Conn7);
    while($row=sql_fetch_assoc($mres)){
        $mpname_arr[]=$row["PRODUCT_DESCRIPTION_SHORT"];
    }

    $html.="<tr style='text-align:center;'>";
    $html.="<td>";
    for($i=0; $i<count($mpname_arr); $i++){
        $html.=$mpname_arr[$i]."<br>";
    }
    $html.="</td>";
    $html.="<td>";
    for($i=0; $i<count($qty_arr); $i++){
        $html.=$qty_arr[$i]."<br>";
    }
    $html.="</td>";
    $html.="<td>".number_format($total_price)."</td>";
    $html.="<td>".$addr."</td>";
    $html.="<td>";
    if($status=='0') {
        $html.= "구매신청";
    }else if($status=='1'){
        $html.= "구매승인";
    }else if($status=='2'){
        $html.= "결제완료";
    }
    $html.="</td>";
    $html.="</tr>";

    $msg["rows"]=$html;
    echo json_encode($msg);
}
?>