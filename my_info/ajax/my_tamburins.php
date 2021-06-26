<?php
include_once "_conf.php";


$total_amount=0;    //구매 총액(결제한 항목)
$application_amount=0;  //신청 금액(결제되지 않은 항목)
$balance_amount=0;  //잔여 총액(연 제한금액 - 구매 총액)
$check=0;   //오류체크

$sql="SELECT PA_IDX, PAYMENT_CMPL, PRICE FROM `gm_intranet`.ProductApplies_TB AS PA
      INNER JOIN `gm_intranet`.ProductItems_TB AS `PI` USING(PA_IDX)
      WHERE PGM_IDX='".$_SESSION['PGM_IDX']."' AND PAYMENT_CMPL IN (0,1,2,3,5) AND PA.REGDATE >= '".date('Y')."-01-01 00:00:00' AND PA.REGDATE <= '".date('Y-m-d')." 23:59:59'
      ORDER BY PA.PA_IDX DESC";
$res=sql_query($sql,null,$Conn4);


while($data=sql_fetch_assoc($res)) {
    switch ($data["PAYMENT_CMPL"]) {
        case 0:
        case 1:
        case 2:
            $application_amount += $data["PRICE"];
            break;
        case 3:
        case 5:
            $total_amount += $data["PRICE"];
            break;
        default:
            $check++;
            alert("구매 금액 집계도중 오륙 발생했습니다. IT기획파트에 문의바랍니다.");
            break;
    }
}
$balance_amount = 5000000-$total_amount;
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">탬버린즈 상품 구매현황</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive myinfo-num-size my-tamburins-info">
                <table class="input-table " style="width:fit-content; position: relative;">
                    <tr>
                        <th class="info-table-th-size">구매 총액</th>
                        <td><span style="width:80px;"><?=number_format($total_amount)?></span></td>
                    </tr>
                    <tr>
                        <th>신청 금액</th>
                        <td><span style="width:80px;"><?=number_format($application_amount)?></span></td>
                    </tr>
                    <tr>
                        <th style="padding-bottom: 15px;">잔여 총액</th>
                        <td style="padding-bottom: 15px;"><span style="width:80px;"><?=number_format($balance_amount)?></span></td>
                    </tr>
                    <!-- <div class="sub-table-bottom" id="myinfo_tamburins_coment" style="width:fit-content; float:right; display:none;">
                        <div class="sub-table-info">
                            <p>
                                * 한도 금액은 실 결제액 기준 연 500만 원이며 올해 1월 1일 ~ 12월 31일까지의 금액만 측정합니다.<br/>
                                * 연도가 바뀌면 구매 총액은 초기화됩니다.
                            </p>
                            <p style="color:red;">
                                * 결제가 완료되지 않은 신청에 대한 금액은 구매 총액에 포함되지 않습니다.
                            <p>
                        </div>
                    </div> -->
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .my-tamburins-info tbody th{
        width:330px;
    }
</style>