<?php
include "_conf.php";



define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

/** Include PHPExcel */
require_once '/home/welfare_new/www/Classes/PHPExcel.php';
if($type=="normal"){



// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties

$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
    ->setLastModifiedBy("Maarten Balliauw")
    ->setTitle("PHPExcel Test Document")
    ->setSubject("PHPExcel Test Document")
    ->setDescription("Test document for PHPExcel, generated using PHP classes.")
    ->setKeywords("office PHPExcel php")
    ->setCategory("Test result file");


$sql = "SELECT c.RcmmndrRank,c.UserId,c.UserName,DATE_FORMAT(a.REGDATE,'%m') AS month,floor(SUM(d.K_PRICE)) AS sum_price FROM ProductApplies_copy AS a 
LEFT JOIN ProductItems_copy AS b USING(PA_IDX) 
LEFT JOIN global.GLOBAL_MEMBER AS c USING(PGM_IDX) 
LEFT JOIN global.MASTER_PRODUCT AS d USING(PCODE)
WHERE DATE_FORMAT(a.REGDATE,'%Y')='$year' AND b.FLAG=1 and a.GROUP='member' GROUP BY PGM_IDX,month ORDER BY month,PGM_IDX";



$res=sql_query($sql,null,$Conn4);


$objPHPExcel->getActiveSheet()->freezePane('A2');
$styleArray= array(
    'font'=> array(
        'color'=> array('rgb'=> '000000'),
        'size'=> 9,
        'name'=> 'Malgun gothic'
    ));
$objPHPExcel->getDefaultStyle()
    ->applyFromArray($styleArray);
$fromCol='A';
$toCol='G';
for($i = $fromCol; $i !== $toCol; $i++) {
    $objPHPExcel->getActiveSheet()->getColumnDimension($i)->setWidth(15);
}

$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->setActiveSheetIndex(0)->setTitle($year.'년 상품복지 지급금액');
$objPHPExcel->getActiveSheet()->setAutoFilter('A1:F1');
$objPHPExcel->getActiveSheet()
    ->setCellValue('A1','헬로인사 NO.')
    ->setCellValue('B1','사번')
    ->setCellValue('C1','신청자')
    ->setCellValue('D1','구분(단위)')
    ->setCellValue('E1','지급월')
    ->setCellValue('F1','금액')

;






if(sql_num_rows($res)==0){
    $tmpArray = array();

        $tmpArray[]=array(
            'od_id' => "no data"
        );

}else{
    $tmpArray = array();
    $k=2;
    for($i=1;$i<=$data=sql_fetch_assoc($res);$i++){
        /** 아래 배열을 엑셀에 쓸 것입니다. **/
        
        $tmpArray[]=array(
            'RcmmndrRank' => $data['RcmmndrRank']
        ,'UserId' => $data['UserId']
        ,'UserName' => $data['UserName']
        ,'d' => "제품복지(원)"
        ,'month' => $data['month']
        ,'sum_price' => number_format($data['sum_price'])
        );
        $k++;

    }
}


/** 위 배열 $tmpArray 를 A2 부터 차례대로 쓴다는 말입니다. **/
$objPHPExcel->getActiveSheet()->fromArray($tmpArray, NULL, 'A2');


// Save Excel 2007 file

/** 위에서 쓴 엑셀을 저장하고 다운로드 합니다. **/

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.date("Y년m월d일").'-'.$year.'년 상품복지 지급금액.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');

}

?>