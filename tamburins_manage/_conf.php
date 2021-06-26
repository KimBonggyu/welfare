<?php
define("rootDir","../../");
include rootDir."inc/common.php";
include rootDir."fnc/common.php";

$tam_flag = array("0"=>"구매신청", "1"=>"구매승인(결제대기)", "2"=>"결제진행중", "3"=>"결제완료(배송대기)", "4"=>"구매취소", "5"=>"배송처리(완료)", "8"=>"구매반려", "9"=>"구매기간 만료");
?>