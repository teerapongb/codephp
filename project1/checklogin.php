#!/usr/bin/php
<?php
date_default_timezone_set("GMT");

if(!isset($argv[1])){
	$argv[1]="";
}

//---use function---------
checkArg($argv[1]);
calWeekend($sdate,$edate,$day,$holiday);
checkUserLogin($sdate,$edate,$day,$weekends,$filename);
countName($arrUniq);
maxLogin($namemax,$dataArr,$numAvg);
writeCsv($loginall,$dataArr,$minMaxAvg,$totalUser,$namedate,$week,$holiday,$arrNoUniq,$minMaxAvgNoUniq,$arrNumLogin);
writeCsv_test($loginall,$arrSortNameLogin);

function checkArg($argv){
	$options=$argv;
	global $filename,$holiday,$sdate,$edate,$day;
	if($options=="-i"){
		$ckfile=file_exists("filename.txt");
		if($ckfile==0){
			exit('Enable to open file "filename.txt"\n');
		}
		$file_n=fopen("filename.txt","r") or dir("Enable to open file");
		while(!feof($file_n)){
        		$data=fgets($file_n);
                	//explode string to array       
                	$arrData=explode("=",$data);
			if($arrData[0]=="filename"){
				$filename=substr($arrData[1],0,-1);
			}else if($arrData[0]=="holiday"){
				$holiday=$arrData[1];
				//$holiday=substr($arrData[1],0,-1);
			}else if($arrData[0]=="startdate"){
				$sdate=substr($arrData[1],0,-1);
			}else if($arrData[0]=="enddate"){
				$edate=substr($arrData[1],0,-1);
			}else{}
		}
		fclose($file_n);
		$countday=strtotime($edate)-strtotime($sdate);
        	$day=ceil($countday/(60*60*24));

	}else{
		//---input file name
		$filename=readline("Input path and file name such as /home/script/windows.log Enter to Default file windows.log :");
		if($filename==""){
			$filename="windows.log";
		}
		$chk_file=fopen($filename,"r") or dir("Enable to open file");
		inputDate();
	}
}


//---function input start_date and end_date
function inputDate(){
	global $sdate,$edate,$day;
	$start_date= readline("Input start date in format yyyy-mm-dd :");
	if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$start_date)){	
		$def_date=date('Y-m-d',strtotime($start_date. "+30 days"));
		$end_date= readline("Input end date in format yyyy-mm-dd or enter to default ".$def_date." :");
		if($end_date==""){
			$sdate=$start_date;
                        $edate=$def_date;
                        $day=30;
			addHoliday();
		}else if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$end_date)){
			addHoliday();	
			$sdate=$start_date;
			$edate=$end_date;
			//---calculate day between start_date to end_date
			$countday=strtotime($edate)-strtotime($sdate);
			$day=ceil($countday/(60*60*24));
			if($day<0){
				echo "should insert end date more than start date!!!";
				inputData();	
			}else{}
		}else{
			echo "Insert Wrong format!!!\n";
			inputDate();			
		}
	}else{
		echo "Insert Wrong format!!!\n";
		inputDate();
	}
}


//---function add holiday
function addHoliday(){
	global $holiday;
	$ans= readline("Do you want add more holiday? Answer:y/n default n : "); 
	if($ans==""){
		$ans="n";
	}
	if($ans=="y"){
		$date_holiday = readline("Input holiday in format yyyy-mm-dd : ");
		if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date_holiday)){
			$holiday.=$date_holiday.",";
			addHoliday();
		}else{
			echo "Insert Wrong format";
			addHoliday();
		}
	}else if($ans=="n"){
		echo $holiday;
	
	}
}


//---calculate weekend and holiday
function calWeekend($start_d,$end_d,$countday,$holidays){
	global $weekends,$week;
	$weekend="";
	$start_d=$start_d;
	$end_d=$end_d;
	$countday=$countday;
	$holidays=$holidays;
	$week_e;
	for($i=0;$i<=$countday;$i++){
		$date1=date('Y-m-d',strtotime($start_d."+".$i." days"));
		$day=date('l', strtotime($date1));
		//echo $date1."-->".$day."\n";	
		if($day=="Sunday" || $day=="Saturday"){
			$weekend.=$date1.',';
		}
	}
	if(strlen($holidays>0)){
		$holidays=substr($holidays,0,-1);
		$week_e=$weekend.$holidays;
        }
	else{
		$week_e=substr($weekend,0,-1);
	}
	$week=substr($weekend,0,-1);
//	echo $week_e;
	$weekends=explode(",",$week_e);
print_r($weekends);	
}


//---check user login uniq --arrUniq array 2 di
function checkUserLogin($sdate,$edate,$day,$weekends,$filename){
	$sdate=$sdate;
	$weekends=$weekends;
	$day=$day;
	$date1=$sdate;
	$edate=$edate;
	$num1=$n=0;
	$numUserUnique=$numUserNoUniq=$numU_no_uniq=0;
	global $arrUniq,$numAvg,$arrNoUniq,$numUserNoUniq,$minMaxAvgNoUniq;
        $arrUniq=array();
	$dataAll="";
	//read log file
//	for($c=0;$c<=$day;$c++){
		$nameArr=array();
//		$date2=date('Y-m-d',strtotime($sdate."+".$c." days"));
//		$filename="login_".$date2.".log";
	        
			$file = fopen("$filename","r");
		
			$replace_from=array("SINDCEAP","_Profile - ","login from ");
			$replace_to=array(",",",",",");
			$strname="";
			while(!feof($file)){
				$data=fgets($file);
				
			        $pos=strpos($data,"login from");
				if($pos===false){
				}
				else{	
					$data_rep=str_replace($replace_from,$replace_to,$data);
				
					//explode string to array	
					$arrData=explode(",",$data_rep);
						
					$edateadd1=date('Y-m-d',strtotime($edate."+ 1 days"));
					$date_time_rep=str_replace("T",",",$arrData[0]);
					$date_time=explode(",",$date_time_rep);
					if($date_time[0]>=$sdate && $date_time[0]<=$edateadd1){
							if(!isset($date_time[1])){
							$date_time[1]="";
						}
						$time1=substr($date_time[1],0,8);
						if(!isset($arrData[2])){
							$arrData[2]="";
							$arrData[3]="";
						}
						$login_name=substr($arrData[2],0,-1);
						if(preg_match("/^[0-9]$/",substr($login_name,0,1))){
							$login_name= "'".$login_name;
						}
						$ipaddr=substr($arrData[3],0,-2);

						$checkw="no";
	        				for($i=0;$i<=count($weekends)-1;$i++){
	                        			if($date1==$weekends[$i]){
	                                			$checkw="yes";
	
	                        			}else{}
	                			} 	
						if($date_time[0]==$date1){
							
							$strname .= $login_name.",";
							//echo $strname."\n";
						}else{
								
							if(substr_count($strname,",")>0){							
								$strname1=substr($strname,0,-1);
							}
							//$strname1=$strname;			
							$arrName= explode(",",$strname1);
							$uniqName=array_unique($arrName);
							$countName=count($uniqName);
							$countNoUniq=count($arrName);
							$nameArrToStr=implode(",",$uniqName);
							$strArrName=implode(",",$arrName);
							if($checkw=="no"){
	                             				$numUserUnique=$numUserUnique+$countName;
								$max_no_u[$num1]=$countNoUniq;	
								$numU_no_uniq+=$countNoUniq;	
	                                			$num1=$num1+1;
								
	                        			}
							$arrUniq[$n]=$date1.",".$countName.",".$nameArrToStr;
							//$arrUniq_tmp=explode(",",$dataAll);
							//$arrUniq[$n]=array($arrUniq_tmp);
							$arrNoUniq[$n]=$date1.",".$countNoUniq.",".$strArrName;
							$numUserNoUniq+=$countNoUniq;	
							$min_no_u[$n]=$countNoUniq;
							$n++;
							$date1=$date_time[0];
							$strname=$login_name.",";


						}
					}
				}
		
			
			}				
			fclose($file);
//	print_r($arrUniq);
	$num2=($day+1)-count($weekends);
	$numAvg=$numUserUnique/$num2;
	$numAvgNoUniq=$numU_no_uniq/$num2;
	$maxUserNoUniq=max($max_no_u);
	echo "sum=".$numUserUnique."/ days=".$num2."= ".$numAvg."\n";
	echo "sum=".$numU_no_uniq."/ days=".$num2."= ".$numAvgNoUniq."\n";
	$minUserNoUniq=min($min_no_u);
	$minMaxAvgNoUniq=array(array("Min.",$minUserNoUniq),array("Max.",$maxUserNoUniq),array("Avg.",$numAvgNoUniq),array("Total.",$numUserNoUniq));
}

//---count name by date and uniq name all 
function countName($dataAll){
	
	//dataArr keep data array 2 di	
	//loinall keep data user login unique
	//namemax keep total name login by date
	global $dataArr,$namemax,$loginall,$totalUser,$arrNumLogin,$arrSortNameLogin;	
	$tmp=$dataAll;
	$loginLess10=$loginMore10=$loginMore15=$loginMore20=0;
	//$tmp = explode('\n',$dataAll);
	$data= array();
	$name_value_login="";
	foreach($tmp as $k => $v)
	{
		$data[] = explode(',',$v);
	
	}
	$numrow= count($data);
	for($row=0;$row<$numrow;$row++){
		$numcol=count($data[$row]);
		for($col=2;$col<$numcol;$col++){
                        if($data[$row][$col]!=""){			
				$data2[]=$data[$row][$col];
			}
		}
	}
	//sort array name from max to min login
	$result=array_count_values($data2);
	arsort($result);
	foreach($result as $x => $x_value) {
//	     $namevalue.=$x.",".$x_value."\n";
	     if($x!=""){
	     $namemax.=$x.",";
	     $name_value_login.=$x.",".$x_value."\n";
		if($x_value<10){
			$loginLess10++;	
		}
		if($x_value>=10){
			$loginMore10++;
		}
		if($x_value>=15){
			$loginMore15++;
		}
		if($x_value>=20){
			$loginMore20++;
		}
	}
		
	}
	$arrNumLogin=array(array("login < 10",$loginLess10),array("login >= 10",$loginMore10),array("login >= 15",$loginMore15),array("login >= 20",$loginMore20));
       // echo $name_value_login;
	$arrSortNameLogin=explode("\n",$name_value_login);
	//print_r($arrSortNameLogin);
	$strName=implode(",",$result);
	$dataArr=$data;
	$data2=array_unique($data2);
	$loginall=$data2;
	$totalUser=array(array("Total",count($loginall)));
}


//---check top 10 login and max,min,avg
function maxLogin($name,$dataAll,$numAvg1){
	global $namedate,$minMaxAvg;
	$strname=$name;
//	echo "strName".$strname;
	$numAvg1=$numAvg1;
	$arrname=explode(",",$strname);
	$name_m1=$arrname[0];
	$name_m2=$arrname[1];
	$name_m3=$arrname[2];
	$name_m4=$arrname[3];
	$name_m5=$arrname[4];
	$name_m6=$arrname[5];
	$name_m7=$arrname[6];
	$name_m8=$arrname[7];
	$name_m9=$arrname[8];
	$name_m10=$arrname[9];
	$namedate1="";
	$datename1=$datename2=$datename3=$datename4=$datename5=$datename6=$datename7=$datename8=$datename9=$datename10="";
	$data=$dataAll;
//	$numuser=0;
	$countUser=array();
        $numrow= count($data);
        for($row=0;$row<$numrow-1;$row++){
                $numcol=count($data[$row]);
		$date=$data[$row][0];
		$user=$data[$row][1];
		$countUser[$row]=$user;
	//	$numuser=$numuser+$user;
                for($col=2;$col<$numcol;$col++){
         	        $name=$data[$row][$col];
			switch($name){
				case $name_m1:
					$datename1.=$date.",";
					break;
				case $name_m2:
                                        $datename2.=$date.",";
                                        break;
				case $name_m3:
                                        $datename3.=$date.",";
                                        break;
				case $name_m4:
                                        $datename4.=$date.",";
                                        break;
				case $name_m5:
                                        $datename5.=$date.",";
                                        break;
				case $name_m6:
                                        $datename6.=$date.",";
                                        break;
				case $name_m7:
                                        $datename7.=$date.",";
                                        break;
				case $name_m8:
                                        $datename8.=$date.",";
                                        break;
				case $name_m9:
                                        $datename9.=$date.",";
                                        break;
				case $name_m10:
                                        $datename10.=$date.",";
                                        break;

				default;
			}
		
		}
        }
	
       
	$namedate1.=$name_m1.",".substr_count($datename1,",").",".$datename1."\n".$name_m2.",".substr_count($datename2,",").",".$datename2."\n".$name_m3.",".substr_count($datename3,",").",".$datename3."\n".$name_m4.",".substr_count($datename4,",").",".$datename4."\n".$name_m5.",".substr_count($datename5,",").",".$datename5."\n".$name_m6.",".substr_count($datename6,",").",".$datename6."\n".$name_m7.",".substr_count($datename7,",").",".$datename7."\n".$name_m8.",".substr_count($datename8,",").",".$datename8."\n".$name_m9.",".substr_count($datename9,",").",".$datename9."\n".$name_m10.",".substr_count($datename10,",").",".$datename10;
	//$numuser=$numuser/($numrow-1);
	$namedate=explode("\n",$namedate1);
	print_r($countUser);
	$minMaxAvg=array(array("Min.",min($countUser)),array("Max.",max($countUser)),array("Avg.",$numAvg1));
}

function writeCsv_test($loginAll,$arrSortNameLogin){
	$loginAll=$loginAll;
	$arrSortNameLogin=$arrSortNameLogin;
	$strN="";
	$wfile=fopen("userunique.csv","w");
	
	fputcsv($wfile,array("Unique name"));
	foreach($loginAll as $line){
		fputcsv($wfile,explode(",",$line));
	}
	fputcsv($wfile,array(" "));
	fputcsv($wfile,array("Name","Total login"));
	for($i=0;$i<count($arrSortNameLogin);$i++){
                $strN=$arrSortNameLogin[$i];
                fputcsv($wfile,explode(",",$strN));
        }

}

//---write data to csv file
function writeCsv($loginAll,$dataArr,$minMaxAvg,$totalUser,$nameDate,$weeks,$holidays,$arrNoUniq,$minMaxAvgNoUniq,$arrNumLogin){
	$loginAll=$loginAll;
	$dataArr=$dataArr;
	$minMaxAvg=$minMaxAvg;
	$totalUser=$totalUser;
	$nameDate=$nameDate;
	$weeks=$weeks;
	$arrNoUniq=$arrNoUniq;
	$minMaxAvgNoUniq=$minMaxAvgNoUniq;
	$arrNumlogin=$arrNumLogin;
	$date=date("Y-m-d");
	$holidays=$holidays;
	$h_w_str="Weekends,".$weeks."\n"."Holidays,".$holidays;
	$file="report_".$date.".csv";	
	$wfile=fopen($file,"w");
	$h_weeks=explode("\n",$h_w_str);
	$titleLogin=array("Unique login per day");
	$titleTop=array("Top 10 login");
	$newLine=array(" ");
	$headTop=array(array("User","Total login"));
	
	foreach($h_weeks as $line4){
		fputcsv($wfile,explode(",",$line4));
	}
	
	fputcsv($wfile,$newLine);
	fputcsv($wfile,$titleLogin);
	foreach($dataArr as $line1){
		fputcsv($wfile,$line1);
	}
	foreach($minMaxAvg as $line2){
		fputcsv($wfile,$line2,"\t");
	}
	foreach($totalUser as $line){
		fputcsv($wfile,$line,"\t");
	}
	fputcsv($wfile,$newLine);
	fputcsv($wfile,$titleTop);
	foreach($headTop as $line3){
		fputcsv($wfile,$line3);
	}
	foreach($nameDate as $linename){
		fputcsv($wfile,explode(",",$linename));
	}
	fputcsv($wfile,$newLine);
	fputcsv($wfile,array("All user login per day"));	
	for($i=0;$i<count($arrNoUniq);$i++){
		$strN=$arrNoUniq[$i];
		fputcsv($wfile,explode(",",$strN));
	}
	
	fputcsv($wfile,$newLine);
	foreach($minMaxAvgNoUniq as $line5){	
		fputcsv($wfile,$line5,"\t");
	}
	fputcsv($wfile,$newLine);
	fputcsv($wfile,array("Period login","total user"));
	foreach($arrNumLogin as $line6){
		fputcsv($wfile,$line6,"\t");
	}
	fclose($wfile);
}

?>
