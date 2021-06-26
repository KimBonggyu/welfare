<?php
include "_conf.php";
include rootDir . "layout/head.php";

if($mode=="excel_add"){
    
    
    
    
    $uploads_dir = 'upload';
    $allowed_ext = array('jpg','jpeg','png','gif');

    // 변수 정리
    $error = $_FILES['file_excel']['error'];
    $name = $_FILES['file_excel']['name'];

    $first = array_shift(explode('.', $name));
    $ext = array_pop(explode('.', $name));
    $f_name =$first."_".preg_replace("/[^0-9]*/s", "", date("Y-m-d H:i:s"));
    
    move_uploaded_file( $_FILES['file_excel']['tmp_name'], "$uploads_dir/$f_name.$ext");
    

    
    
    
    set_include_path(get_include_path() . PATH_SEPARATOR . rootDir.'Classes/');
    
    require_once "PHPExcel.php"; // PHPExcel.php을 불러와야 하며, 경로는 사용자의 설정에 맞게 수정해야 한다.
    $objPHPExcel = new PHPExcel();
    require_once "PHPExcel/IOFactory.php"; // IOFactory.php을 불러와야 하며, 경로는 사용자의 설정에 맞게 수정해야 한다.
    $filename = $uploads_dir."/".$f_name.".".$ext; // 읽어들일 엑셀 파일의 경로와 파일명을 지정한다.
    $check=0;
    $overcheck=0;
    try {
        // 업로드 된 엑셀 형식에 맞는 Reader객체를 만든다.
        $objReader = PHPExcel_IOFactory::createReaderForFile($filename);
        // 읽기전용으로 설정
        $objReader->setReadDataOnly(true);
        // 엑셀파일을 읽는다
        $objExcel = $objReader->load($filename);
        // 첫번째 시트를 선택
        $tot_qty=0;
        for ($j = 0 ; $j < 1 ; $j++) {//시트 셋팅
            $objExcel->setActiveSheetIndex($j);
            $objWorksheet = $objExcel->getActiveSheet();
            $rowIterator = $objWorksheet->getRowIterator();
            foreach ($rowIterator as $row) { // 모든 행에 대해서
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
            }
            $maxRow = $objWorksheet->getHighestRow();

            if($maxRow>10000){alert("잘못된 엑셀입니다. 이재영에게 문의주세요.");}

            for ($i = 2 ; $i <= $maxRow ; $i++) {//행 긁어오기
                $product_code= $objWorksheet->getCell('A' . $i)->getValue(); // A열

                $checksql = "SELECT * FROM `gm_intranet`.AvailableProduct_TB WHERE PCODE = '".$product_code."'";
                $checkres = sql_query($checksql, null, $Conn4);

                if(sql_num_rows($checkres)){
                    $overcheck++;
                }else{
                    $sql="SELECT * FROM `Master_Data`.Master_Product_copy WHERE P_CODE = '".$product_code."'";
                    $data=sql_fetch($sql, null, $Conn7);

                    if($data){
                        $ssql = "INSERT `gm_intranet`.AvailableProduct_TB set PCODE = '".$data["P_CODE"]."', P_88CODE = '".$data["P_88CODE"]."', P_NAME = '".$data["PRODUCT_DESCRIPTION_SHORT"]."', REGDATE = NOW()";
                        if(sql_query($ssql,null,$Conn4)){
                            $check++;
                        }
                    }
                }
            }
        }

    }

    catch (exception $e) {
        echo '엑셀파일을 읽는도중 오류가 발생하였습니다.';
    }
    if($check || $overcheck){
        alert("정상등록 ".$check." 건 처리되었습니다. \\r\\n중복된 상품등록 ".$overcheck." 건  처리되었습니다.");
    }else{
        alert("처리중 오류가 발생하였습니다.");
    }
    
}
?>

