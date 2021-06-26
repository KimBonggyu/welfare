<?php
include "_conf.php";
include_once rootDir . "inc/common.php";
include_once rootDir . "fnc/common.php";

/** Include PHPExcel */
require_once rootDir . 'Classes/PHPExcel.php';


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

$bsql = "SELECT *,a.REGDATE as regdate,b.FLAG as flag , a.PHONE as deli_phone FROM ProductApplies_copy AS a LEFT JOIN ProductItems_copy AS b USING(PA_IDX)
        LEFT JOIN global.GLOBAL_MEMBER AS c USING(PGM_IDX) LEFT JOIN global.MASTER_PRODUCT AS d using(PCODE)
        where  DATE_FORMAT(a.REGDATE,'%Y')='$year' AND b.FLAG=1 and a.GROUP='member' ";
$bres = sql_query($bsql);
$bnum = sql_num_rows($bres) + 3;


$objPHPExcel->setActiveSheetIndex(0);
$objPHPExcel->getActiveSheet()
    ->mergeCells('A1:B1')
    ->mergeCells('C1:D1')
    ->mergeCells('F1:F2')
    ->mergeCells('G1:G2')
    ->setCellValue('A1', '입고매장')
    ->setCellValue('A2', '매장코드')
    ->setCellValue('B2', '매장명')
    ->setCellValue('C1', '출고매장')
    ->setCellValue('C2', '매장코드')
    ->setCellValue('D2', '매장명')
    ->setCellValue('E1', '상품')
    ->setCellValue('E2', '상품코드')
    ->setCellValue('F1', '수량')
    ->setCellValue('G1', '기타사항');


if (sql_num_rows($bres) == 0) {

} else {
    $tmpArray = array();
    for ($i = 1; $i <= $data = sql_fetch_assoc($bres); $i++) {

        /** 아래 배열을 엑셀에 쓸 것입니다. **/

        $tmpArray[] = array(
            "매장코드(I)" => "0000"
        , '매장명(I)' => "젠틀몬스터 본사"
        , '매장코드(O)' => $data['POS_CODE']
        , '매장명(O)' => $data['S_NAME']
        , '상품코드' => $data['PCODE']
        , '수량' => $data['QTY']
        );
    }


}


/** 위 배열 $tmpArray 를 A2 부터 차례대로 쓴다는 말입니다. **/
/** 각 시트 너비와 정렬등 스타일 설정 **/
$objPHPExcel->getActiveSheet()->fromArray($tmpArray, NULL, 'A4');
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(38);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(38);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(75);
$objPHPExcel->getActiveSheet()->getStyle('G2:G' . $bnum)->getAlignment()->setWrapText(true);
$objPHPExcel->getActiveSheet()->getStyle('A1:G2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00BFFF');
$objPHPExcel->getActiveSheet()->getStyle('A1:G' . $bnum)->getAlignment()
    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

// Rename worksheet

// Save Excel 2007 file

/** 위에서 쓴 엑셀을 저장하고 다운로드 합니다. **/

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SSG POS_' . date("Ymd") . '.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');


?>