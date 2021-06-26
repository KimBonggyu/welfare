<?php

include 'function.php';
/*그누보드 참조*/
// mysqli_query 와 mysqli_error 를 한꺼번에 처리
// mysql connect resource 지정 - 명랑폐인님 제안
function sql_query($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
    global $Conn;

    if(!$link)
        $link = $Conn;

    // Blind SQL Injection 취약점 해결
    $sql = trim($sql);
    // union의 사용을 허락하지 않습니다.
    //$sql = preg_replace("#^select.*from.*union.*#i", "select 1", $sql);
    $sql = preg_replace("#^select.*from.*[\s\(]+union[\s\)]+.*#i ", "select 1", $sql);
    // `information_schema` DB로의 접근을 허락하지 않습니다.
    $sql = preg_replace("#^select.*from.*where.*`?information_schema`?.*#i", "select 1", $sql);

    if(function_exists('mysqli_query') ) {
        if ($error) {
            $result = @mysqli_query($link, $sql) or die("<p>$sql<p>" . mysqli_errno($link) . " : " .  mysqli_error($link) . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
        } else {
            $result = @mysqli_query($link, $sql);
        }
    } else {
        if ($error) {
            $result = @mysql_query($sql, $link) or die("<p>$sql<p>" . mysql_errno() . " : " .  mysql_error() . "<p>error file : {$_SERVER['SCRIPT_NAME']}");
        } else {
            $result = @mysql_query($sql, $link);
        }
    }

    return $result;
}
//////////실제 ip 찾아내기/////////////
function getRealIpAddr(){
	if(!empty($_SERVER['HTTP_CLIENT_IP']) && getenv('HTTP_CLIENT_IP')){
		return $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && getenv('HTTP_X_FORWARDED_FOR')){
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	elseif(!empty($_SERVER['REMOTE_HOST']) && getenv('REMOTE_HOST')){
		return $_SERVER['REMOTE_HOST'];
	}
	elseif(!empty($_SERVER['REMOTE_ADDR']) && getenv('REMOTE_ADDR')){
		return $_SERVER['REMOTE_ADDR'];
	}
	return false;
}
$_SERVER['REMOTE_ADDR']=getRealIpAddr();//실제 아이피 변경


// 쿼리를 실행한 후 결과값에서 한행을 얻는다.
function sql_fetch($sql, $error=G5_DISPLAY_SQL_ERROR, $link=null)
{
    global $Conn;

    if(!$link)
        $link = $Conn;

    $result = sql_query($sql, $error, $link);
    //$row = @sql_fetch_array($result) or die("<p>$sql<p>" . mysqli_errno() . " : " .  mysqli_error() . "<p>error file : $_SERVER['SCRIPT_NAME']");
    $row = sql_fetch_array($result);
    return $row;
}


// 결과값에서 한행 연관배열(이름으로)로 얻는다.
function sql_fetch_array($result)
{
    if(function_exists('mysqli_fetch_assoc'))
        $row = @mysqli_fetch_assoc($result);
    else
        $row = @mysql_fetch_assoc($result);

    return $row;
}

// 결과값에서 한행 연관배열(이름으로)로 얻는다.
function sql_fetch_assoc($result)
{
    if(function_exists('mysqli_fetch_assoc'))
        $row = @mysqli_fetch_assoc($result);
    else
        $row = @mysql_fetch_assoc($result);

    return $row;
}


// $result에 대한 메모리(memory)에 있는 내용을 모두 제거한다.
// sql_free_result()는 결과로부터 얻은 질의 값이 커서 많은 메모리를 사용할 염려가 있을 때 사용된다.
// 단, 결과 값은 스크립트(script) 실행부가 종료되면서 메모리에서 자동적으로 지워진다.
function sql_free_result($result)
{
    if(function_exists('mysqli_free_result'))
        return mysqli_free_result($result);
    else
        return mysql_free_result($result);
}

function sql_password($value)
{
    // mysql 4.0x 이하 버전에서는 password() 함수의 결과가 16bytes
    // mysql 4.1x 이상 버전에서는 password() 함수의 결과가 41bytes
    $row = sql_fetch(" select password('$value') as pass ");

    return $row['pass'];
}



function sql_insert_id($link=null)
{
    global $Conn;
    if(!$link)
        $link = $Conn;

    if(function_exists('mysqli_insert_id') )
        return mysqli_insert_id($link);
    else
        return mysql_insert_id($link);
}

function sql_num_rows($result)
{
    if(function_exists('mysqli_num_rows'))
        return mysqli_num_rows($result);
    else
        return mysql_num_rows($result);
}

function sql_field_names($table, $link=null)
{
    global $Conn;

    if(!$link)
        $link = $Conn;

    $columns = array();

    $sql = " select * from `$table` limit 1 ";
    $result = sql_query($sql, $link);

    if(function_exists('mysqli_fetch_field')) {
        while($field = mysqli_fetch_field($result)) {
            $columns[] = $field->name;
        }
    } else {
        $i = 0;
        $cnt = mysql_num_fields($result);
        while($i < $cnt) {
            $field = mysql_fetch_field($result, $i);
            $columns[] = $field->name;
            $i++;
        }
    }

    return $columns;
}



function sql_error_info($link=null)
{
   global $Conn;

    if(!$link)
        $link = $Conn;

    if(function_exists('mysqli_error')) {
        return mysqli_errno($link) . ' : ' . mysqli_error($link);
    } else {
        return mysql_errno($link) . ' : ' . mysql_error($link);
    }
}

// 한글 요일
function get_yoil($date, $full=0)
{
    $arr_yoil = array ('일', '월', '화', '수', '목', '금', '토');

    $yoil = date("w", strtotime($date));
    $str = $arr_yoil[$yoil];
    if ($full) {
        $str .= '요일';
    }
    return $str;
}


//페이징함수
function getDefaultValue($Value, $DefaultValue)
{
	
		if($Value == ""){
			return $DefaultValue;
		}
		else{
		 return $Value;
		}
}


function alert($msg='', $url='')
{
  if (!$msg) $msg = '올바른 방법으로 이용해 주십시오.';

	$msg = str_replace("\n","\\n",$msg);

	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
	echo "<script language='javascript'>alert('$msg');";
    if (!$url)
        echo "history.go(-1);";
    echo "</script>";
    if ($url)
        // 4.06.00 : 불여우의 경우 아래의 코드를 제대로 인식하지 못함
        //echo "<meta http-equiv='refresh' content='0;url=$url'>";
        echo "<script language='JavaScript'> location.replace('$url'); </script>";
    exit;
}


function alert_close($msg,$mode="")
{	if($mode=="reload"){
		echo "<script> window.opener.document.location.reload();</script>";
	}

	echo "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\">";
    echo "<script language='javascript'> alert('$msg');  </script>";
	 echo "<script language='javascript'> window.close();  </script>";
    exit;
}


//날짜간 간격 구하기
function dateType($sdate,$edate){
	$dateInfo; //return 값
	$dateTimeBegion = strtotime($edate); //현재시간
	$dateTimeEnd = strtotime($sdate); //넘어오는 시간
	$dateNum = intval(($dateTimeBegion-$dateTimeEnd)/3600); //계산
	
	$dateInfo = intval($dateNum/24)."";
	
	return $dateInfo; //결과값
}

function monthType($sdate,$edate){
	$dateInfo; //return 값
	
	$sdate = preg_replace("/([0-9]{4})([0-9]{2})/", "\\1-\\2", $sdate);
	$edate = preg_replace("/([0-9]{4})([0-9]{2})/", "\\1-\\2", $edate);

	$dateTimeBegion = strtotime($edate."-02"); //현재시간
	
	$dateTimeEnd = strtotime($sdate."-01"); //넘어오는 시간
	$dateNum = intval(($dateTimeBegion-$dateTimeEnd)/3600); //계산
	
	$dateInfo = intval($dateNum/(365*2))."";
	
	return $dateInfo; //결과값
}


//게시글 날짜 타임 간격
function dateintval($sdate,$edate){
	$dateInfo; //return 값
	$dateTimeBegion = strtotime($edate); //현재시간
	$dateTimeEnd = strtotime($sdate); //넘어오는 시간
	$dateNum = intval(($dateTimeBegion-$dateTimeEnd)/3600); //계산
	if($dateNum < 24){
		//$dateInfo = $dateNum."시간 전";
		$dateInfo = $dateNum;
	}else{
		$dateInfo = intval($dateNum/24)."일 전";
	}
	return $dateInfo; //결과값
}

function injection($str){
	 global $Conn;

	if(function_exists('mysqli_real_escape_string') ) {
		$str=mysqli_real_escape_string($Conn,$str);
	}else{
		$str=mysql_real_escape_string($Conn,$str);
	}


	return $str;
}
function ex_rate($to,$end,$date){
	$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);
	

	$sql="select * from `EXCHANGE_RATE` where `CURRENCY_TO`='".$to."' and `CURRENCY_END`='".$end."' and substring(`DATE`,1,10)='".$date."'";
	
	$res=sql_query($sql);
	$data=sql_fetch_assoc($res);

	$rate=$data['RATE'];

	if(!$rate){
		$sql="select * from `EXCHANGE_RATE` where `CURRENCY_TO`='".$to."' and `CURRENCY_END`='".$end."' and (RATE <> '' or RATE<>0 ) order by `DATE` desc limit 1";
		$res=sql_query($sql);
		$data=sql_fetch_assoc($res);
		$rate=$data['RATE'];
	}

	return $rate;
}




function objectToArray($d) { 
	if (is_object($d)) { 
	// Gets the properties of the given object 
	// with get_object_vars function 
	$d = get_object_vars($d); 
	} 
	  
	if (is_array($d)) { 
	/* 
	* Return array converted to object 
	* Using __FUNCTION__ (Magic constant) 
	* for recursive call 
	*/ 
	return array_map(__FUNCTION__, $d); 
	} 
	else { 
	// Return array 
	return $d; 
	}
}
function arrayToObject($d) { 
if (is_array($d)) { 
/* 
* Return array converted to object 
* Using __FUNCTION__ (Magic constant) 
* for recursive call 
*/ 
return (object) array_map(__FUNCTION__, $d); 
} 
else { 
// Return object 
return $d; 
} 
} 
function Exl2phpTime( $tRes, $dFormat="1900" ) { 
    if( $dFormat == "1904" ) $fixRes = 24107.375; 
    else $fixRes = 25569.375; 
    return intval( ( ( $tRes - $fixRes) * 86400 ) ); 
} 
  
function number2hangul($number){ 

        $num = array('', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'); 
        $unit4 = array('', '만', '억', '조', '경'); 
        $unit1 = array('', '십', '백', '천'); 

        $res = array(); 

        $number = str_replace(',','',$number); 
        $split4 = str_split(strrev((string)$number),4); 

        for($i=0;$i<count($split4);$i++){ 
                $temp = array(); 
                $split1 = str_split((string)$split4[$i], 1); 
                for($j=0;$j<count($split1);$j++){ 
                        $u = (int)$split1[$j]; 
                        if($u > 0) $temp[] = $num[$u].$unit1[$j]; 
                } 
                if(count($temp) > 0) $res[] = implode('', array_reverse($temp)).$unit4[$i]; 
        } 
        return implode('', array_reverse($res)); 
}



function curl_soap($Url){
 
    // is cURL installed yet?
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();
 
    // Now set some options (most are optional)
 
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);
 
    // Set a referer
    //curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");
 
    // User agent
    //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
 
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);
 
    // Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 
    // Download the given URL, and return output
    $output = curl_exec($ch);
 
    // Close the cURL resource, and free system resources
    curl_close($ch);
 
    return $output;
}


function isMobile(){
        $arr_browser = array ("iphone", "android", "ipod", "iemobile", "mobile", "lgtelecom", "ppc", "symbianos", "blackberry", "ipad");
        $httpUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        // 기본값으로 모바일 브라우저가 아닌것으로 간주함
        $mobile_browser = false;
        // 모바일브라우저에 해당하는 문자열이 있는 경우 $mobile_browser 를 true로 설정
        for($indexi = 0 ; $indexi < count($arr_browser) ; $indexi++){
            if(strpos($httpUserAgent, $arr_browser[$indexi]) == true){
                $mobile_browser = true;
                break;
            }
        }
        return $mobile_browser;
}


function statistics_res_price($price){
	$tax=$price / 1.1;
	
	$res=floor($tax/10)*10;
	
	return $res;
}
// UTF-8 문자열 자르기
// 출처 : https://www.google.co.kr/search?q=utf8_strcut&aq=f&oq=utf8_strcut&aqs=chrome.0.57j0l3.826j0&sourceid=chrome&ie=UTF-8
function utf8_strcut( $str, $size, $suffix='...' )
{
	$substr = substr( $str, 0, $size * 2 );
	$multi_size = preg_match_all( '/[\x80-\xff]/', $substr, $multi_chars );
	
	if ( $multi_size > 0 )
		$size = $size + intval( $multi_size / 3 ) - 1;
		
		if ( strlen( $str ) > $size ) {
			$str = substr( $str, 0, $size );
			$str = preg_replace( '/(([\x80-\xff]{3})*?)([\x80-\xff]{0,2})$/', '$1', $str );
			$str .= $suffix;
		}
		
		return $str;
}

function strcut_utf8($str, $len, $checkmb=false, $tail='...') {
    /**
     * UTF-8 Format
     * 0xxxxxxx = ASCII, 110xxxxx 10xxxxxx or 1110xxxx 10xxxxxx 10xxxxxx
     * latin, greek, cyrillic, coptic, armenian, hebrew, arab characters consist of 2bytes
     * BMP(Basic Mulitilingual Plane) including Hangul, Japanese consist of 3bytes
     **/
    preg_match_all('/[\xE0-\xFF][\x80-\xFF]{2}|./', $str, $match); // target for BMP
    
    $m = $match[0];
    $slen = strlen($str); // length of source string
    $tlen = strlen($tail); // length of tail string
    $mlen = count($m); // length of matched characters
    
    if ($slen <= $len) return $str;
    if (!$checkmb && $mlen <= $len) return $str;
    
    $ret = array();
    $count = 0;
    for ($i=0; $i < $len; $i++) {
        $count += ($checkmb && strlen($m[$i]) > 1)?2:1;
        if ($count + $tlen > $len) break;
        $ret[] = $m[$i];
    }
    
    return join('', $ret).$tail;
}

?>