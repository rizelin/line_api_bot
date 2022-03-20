<?php
ini_set('display_errors',0);
require_once('../require/header.php');
require_once('../require/mysql.php');

if( intval(date("d") > 20)){
	$minDatetime = date("Y/m/21 00:00:00");
}else{
	$minDatetime = date("Y/m/21 00:00:00",strtotime("-1 month"));
}
$maxDatetime = date("Y/m/20 23:59:59",strtotime("{$minDatetime} +1 month"));

$sql="SELECT e.`id`,e.`name`, t.`employment_type`,
		CASE WHEN d.`id` IS NOT NULL THEN c1.`company_name`
			WHEN s.`id` IS NOT NULL THEN c2.`company_name`
			ELSE NULL END as company_name
	FROM `employee` as e
		LEFT JOIN `employment` t
			ON e.`employment_id`=t.`id`
		LEFT OUTER JOIN `department` d
			ON e.`department_id`=d.`id`
		LEFT OUTER JOIN `company` as c1
			ON d.`company_id`=c1.`id`
		LEFT OUTER JOIN `store` as s
			ON e.`store_id`=s.`id`
		LEFT OUTER JOIN `company` as c2
			ON s.`company_id`=c2.`id`
  ";
if($res = $_link->query($sql)){
  while($row = $res->fetch_array(MYSQLI_ASSOC)){
    $employees[$row['id']] = $row;
  }
}
foreach($employees as $key => $val){
	$sql="SELECT d.`id`,d.`employee_id`,d.`type`,d.`status_datetime`
		FROM `employee_datetime` as d
			-- MinDateより前の最後の出勤時刻
			LEFT JOIN (
				SELECT d.`id`,d.`type`,d.`status_datetime`,d.`employee_id`
				FROM `employee_datetime` as d
				WHERE d.`employee_id`={$val['id']} AND d.`status_datetime` < '{$minDatetime}'
					AND d.`type`=1
				ORDER BY d.`status_datetime` DESC
				LIMIT 0,1
			) a ON d.`employee_id`=a.`employee_id`
			-- MaxDateより後の最後の時刻
			LEFT JOIN (
				SELECT d.`id`,d.`status_datetime`,d.`employee_id`
				FROM `employee_datetime` as d
				WHERE d.`employee_id`={$val['id']} AND d.`status_datetime` > '{$maxDatetime}'
				ORDER BY d.`status_datetime` DESC
				LIMIT 0,1
			) b ON d.`employee_id`=b.`employee_id`
			-- MaxDateより後の最初の出勤時刻
			LEFT JOIN (
				SELECT d.`id`,d.`status_datetime`,d.`employee_id`
				FROM `employee_datetime` as d
				WHERE d.`employee_id`={$val['id']} AND d.`status_datetime` > '{$maxDatetime}'
					AND d.`type`=1
				ORDER BY d.`status_datetime` ASC
				LIMIT 0,1
			) c ON d.`employee_id`=c.`employee_id`
		WHERE d.`status`=0 AND d.`employee_id`={$val['id']}
			AND (
				(a.`id` IS NOT NULL AND d.`status_datetime` >= (a.`status_datetime` + INTERVAL 1 SECOND) )
				OR
				(a.`id` IS NULL AND d.`status_datetime` >= '{$minDatetime}' )
			)
			AND (
				(c.`id` IS NOT NULL AND d.`status_datetime` <= (c.`status_datetime` - INTERVAL 1 SECOND) )
				OR
				(c.`id` IS NULL AND b.`id` IS NOT NULL AND d.`status_datetime` <= (b.`status_datetime` - INTERVAL 1 SECOND) )
				OR
				(c.`id` IS NULL AND b.`id` IS NULL AND d.`status_datetime` <= '{$maxDatetime}' )
			)
			{$where}
		ORDER BY d.`status_datetime` ASC
	";
	if($res = $_link->query($sql)){
	  	$tmpDatetimes = array();
	  	$tmpType = 0;
	  	$tmpStatus = 0;
	  	//1:mixDatetimeより前, 2:minDatetime以降・maxDatetime以前, 3:maxDatetimeより後

		while($row = $res->fetch_array(MYSQLI_ASSOC)){
		  	if($row['status_datetime'] < $minDatetime){
		  		//minDatetimeより前の打刻

		  		if($row['type']==4){
			  		//退勤[値:4]なら配列初期化
		  			$tmpDatetimes = array();
		  		}else{
		  			$tmpDatetimes[] = $row;
		  		}
		  		$tmpType = $row['type'];
		  		$tmpStatus = 1;

		  	}elseif($row['status_datetime'] >= $minDatetime && $row['status_datetime'] <= $maxDatetime){
		  		//minDatetime以降・maxDatetime以前の打刻

		  		if($tmpStatus==1 && $tmpType!=4){
		  			if($row['type']==1){
		  				//出勤[値:1]ならstatusを2に
			  			$tmpDatetimes[] = $row;
				  		$tmpStatus = 2;
		  			}elseif($row['type']==4){
				  		//退勤[値:4]なら配列初期化
			  			$tmpDatetimes = array();
			  			$tmpStatus = 2;
			  		}else{
			  			$tmpDatetimes[] = $row;
			  		}
		  		}else{
		  			$tmpDatetimes[] = $row;
			  		$tmpStatus = 2;
			  	}
		  		$tmpType = $row['type'];

		  	}else{
		  		//maxDatetimeより後の打刻

		  		if($row['type']==4){
		  			break;
		  		}else{
		  			$tmpDatetimes[] = $row;
			  		$tmpType = $row['type'];
			  		$tmpStatus = 3;
			  	}

		  	}

		}
	  	$employees[$val['id']]['datetimes'] = $tmpDatetimes;
	}
}
echo "<pre>";
var_dump($employees);
echo "</pre>";
?>