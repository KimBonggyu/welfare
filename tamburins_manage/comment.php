
<script type="text/javascript">
	function submit_btn(){
		
		$(".btn-primary").hide();
		document.excel_form.submit();
	}
</script>



<?php
include "_conf.php";

include rootDir . "layout/head.php";
?>
<div class="container-fluid">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h4>신청가능 제품 등록</h4>
                <form name="excel_form" action="excel_pop_php.php"  enctype="multipart/form-data" method="post" autocomplete="off">
                    <input type="hidden" name="mode" value="excel_add">

                    <div class="">
                        <input type="file" id="exampleInputFile" name="file_excel"/>
                        <p class="help-block">등록할 파일을 선택해주세요. <a href="excel/sample.xlsx" >[샘플다운]</a></p>
                        </p>
                    </div>
                    <div style="text-align: center;">
                        <button type="button" class="btn btn-primary" onclick="submit_btn();">등록</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


