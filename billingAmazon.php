<?php

include 'PHPExcel.php';

$row = 1;

$valueData= array();
$count=0;
if (($handle = fopen("awsBilling.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	
	
        $num = count($data);
		if($data[22]== "")
		{
			continue;
		}
		else
		{
		 $row++;
		
			$keyData= array();
			array_push($keyData, $data[22],$data[23],$data[24],$data[25],$data[26], $data[18]);
			array_push($valueData, $keyData);
		
		$count++;
		}
    }
	
    fclose($handle);
}

	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("Arun Verma");
	$objPHPExcel->getProperties()->setTitle("AWS Report");
	$objPHPExcel->getProperties()->setSubject("AWS reporting information");

	$objPHPExcel->setActiveSheetIndex(0);
	$objPHPExcel->getActiveSheet()->SetCellValue('A1', 'Sr No');
	$objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Cost Center');
	$objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Application Name');
	$objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Environment Type');
	$objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Monthly Cost($)');

	$length= count($valueData);
	$finalData= array();
	$testData= array();
	$index=1;
	$appName='';
	$cost=0;
	$costCenter='';
	
	$envType = array();
	$costArray = array();
	$app_count = 0;
	$fg = false;
	$app_name_array = array();
	
	for($k=1; $k<$length; $k++)
	{
				$prodCost=0;
				$nonProdCost=0;
				$instanceData= $valueData[$k];
				$appName= $instanceData[0];
				
				$envName= $instanceData[4];
				$costCenter ="";
				
					$cost=$instanceData[5];
					if($envName=='Prod') 
						{				
							$prodCost = $cost;
						}
						
					if($envName == 'Non-Prod') 
						{
							$nonProdCost	= $cost; 
						}			
			
				
				if(!empty($instanceData[1]))
				{
					$costCenter = $instanceData[1];
				}
				else if(!empty($instanceData[2]))
				{
					$costCenter = $instanceData[2];
				}
				else
				{
					$costCenter = $instanceData[3];
				}
				$prod_count = 0;
				$nprod_count = 0;				
				$newProdCost=0;
				$newNonProdCost=0;
				
				for($x=$k+1; $x < $length; $x++)
					{
						$newInstanceData= $valueData[$x];
						$newAppName= $newInstanceData[0];
						$newEnv= $newInstanceData[4];
						
						if($newInstanceData[4] == 'Prod'){
							$envType[0] = $newInstanceData[4];
						}

						if($newInstanceData[4] == 'Non-Prod'){
							$envType[1] = $newInstanceData[4];
						} 
						
						if(strtolower(trim($appName)) == strtolower(trim($newAppName)))
						{			
							
							if($newEnv == 'Prod')
							{
								$newProdCost= $newProdCost + $newInstanceData[5];
							}
						 	else if($newEnv == 'Non-Prod')
							{								
								$newNonProdCost = $newNonProdCost  + $newInstanceData[5];
							}
							
							else
							{
								continue;
							} 
							
							if($newEnv == 'Prod')
							{
								$prod_count++;
							}
							if($newEnv == 'Non-Prod')
							{
								$nprod_count++;
							}
							
						}
						else{
							continue;
						}
						
						
					}
					
						if($envName == 'Prod')		
							$prodCost= $prodCost + $newProdCost;
						else
							$nonProdCost = $nonProdCost + $newNonProdCost;				
		
				$instanceData = array();
				$costArray= array();
				$mainArray= array();				
				
	 			$costArray['Prod'] 		=  $prodCost;
				$costArray['Non-Prod'] 	= $newNonProdCost; 	
	
		$instanceData1 = array();	
		$instanceData2 = array();	
		$m = 0;
		foreach($envType as $env_key => $env_value)
			{
				if(!empty($appName))
					{
							$instanceData1[$m][] = 	$appName;
							$instanceData1[$m][] = 	$costCenter;
							$instanceData1[$m][] = 	$env_value;
							$instanceData1[$m][] =	$costArray[$env_value];
					
					//		array_push($instanceData1, $appName, $costCenter,$env_value,$costArray['prod']);
						
							if(empty($finalData))
							{
								if($costArray[$env_value] != 0)
								{
									$finalData[] = $instanceData1[$m];
								}
							}						
							
							else
							{	
								$arrayLen = count($finalData);
								$flag= false;
								for($y=0 ;$y < $arrayLen; $y++)
								{
									$instanceArray = $finalData[$y];
									for($z=0; $z < count($instanceArray); $z++)
									{
										if($instanceArray[0] == $appName && in_array($env_value,$instanceArray))
										{
											$flag=true;
											break;
										}
									}
								}
								
								if(!$flag)
									{
										if($costArray[$env_value] != 0)
										{
											array_push($finalData, $instanceData1[$m]);
										}
									}
							}
							
					}
				$m++;	
			}
			
	}
	
/* 	echo "<pre>";
	print_r($finalData);
	exit; 	 */
	
	$ind=1;
	for($index=0; $index < count($finalData); $index++)
	{
		$instance = $finalData[$index];
			$ind++;
				$objPHPExcel->setActiveSheetIndex(0)
			  ->setCellValue('A'.$ind, $ind-1)
			  ->setCellValue('B'.$ind, $instance[1]) 
			  ->setCellValue('C'.$ind, $instance[0])
			  ->setCellValue('D'.$ind, $instance[2])
			  ->setCellValue('E'.$ind, $instance[3]);	 		  
	}
	$objPHPExcel->getActiveSheet()->setTitle('AWS Billing Report');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('billingReport.xls'); 
?>