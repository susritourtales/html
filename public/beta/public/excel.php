<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if($_POST['exp'] == '1'){
        //$url =  'https://www.susritourtales.com/test/public/api/financial-statements';
        $url =  'https://www.susritourtales.com/api/financial-statements';
        $params = array(
            'start_date' => urlencode($_POST['from']),
            'end_date' => urlencode($_POST['to'])
            );

        function httpPost($url,$params)
        {
            $postData = '';
            //create name value pairs seperated by &
            foreach($params as $k => $v) 
            { 
                $postData .= $k . '='.$v.'&'; 
            }
            $postData = rtrim($postData, '&');
            $ch = curl_init();  
        
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_HEADER, false); 
            curl_setopt($ch, CURLOPT_POST, 2);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);    
            $output=curl_exec($ch);
            if(curl_errno($ch)){
                echo 'Curl error: ' . curl_error($ch);
            }
            curl_close($ch);
            return $output;
        }
        $result = httpPost($url, $params);
        $jsonRes = json_decode($result);
        $fsArr = $jsonRes->financialStatement;
        $fsData = array();
        $sno = 1;
        foreach($fsArr as $fs){
            $actualAmt = round(($fs->amount) / (1 + ($fs->GST/100)),2);
            $gst = $fs->amount - $actualAmt;
            $fsData[] = array("Sno"=>$sno, "Date"=>$fs->created_at, "Amount"=>$actualAmt, "GST"=>$gst);
            $sno++;
        }
        /* echo "<pre>";
        print_r($fsData);
        echo "</pre>"; */

        function filterData(&$str)
        {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
        }
        $expData = "";
        $flag = false;
        foreach($fsData as $row) {
            if(!$flag) {
                // display column names as first row
                $expData = $expData . implode("\t", array_keys($row)) . "\n";
                $flag = true;
            }
            // filter data
            //array_walk($row, 'filterData');
            $expData = $expData . implode("\t", array_values($row)) . "\n";

        }
        // file name for download
        $fileName = "FS_" . rand() . ".xls";

        // headers for download
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        echo $expData;
        
        exit();
    }
}
 ?>
 <?php  
 //excel.php  
 /* header('Content-Type: application/vnd.ms-excel');  
 header('Content-disposition: attachment; filename='.rand().'.xls');  
 echo $_GET["data"];  */ 
 ?> 