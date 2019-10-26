<?php
// error_reporting(0);
include("./config.php");

$safePath = "./safe_samples/";
$unsafePath = "./unsafe_samples/";
$safeTokenFile = "./safe_tokens.txt";
$unsafeTokenFile = "./unsafe_tokens.txt";

unlink('unsafe_y.txt');
xxxasdfasdfsdfs_getAllTokens($safePath,$safeTokenFile);
xxxasdfasdfsdfs_getAllTokens($unsafePath,$unsafeTokenFile);
// xxxasdfasdfsdfs_test();


// ()
$bracketNum = 0;
// {}
$bracesNum = 0;
$senEnd = 0;
$varValue = '';

function isSenEnd($char)
{
	global $bracketNum;
	global $bracesNum;
	global $senEnd;
	
	if('(' == $char)
	{
		$bracketNum++;
	}elseif('{' == $char)
	{
		$bracesNum++;
	}
	
	if(';' == $char)
	{
		$senEnd = 1;
		return 1;
	}elseif(')' == $char)
	{
		$bracketNum--;
		if(0 == $bracketNum)
		{
			$senEnd = 1;
			return 1;
		}
	}elseif('}' == $char) 
	{
		$bracesNum--;
		if(0 == $bracesNum)
		{
			$senEnd = 1;
			return 1;
		}
	}

	return 0;
	
}


function xxxasdfasdfsdfs_oneTokenize(&$n,&$tokens,&$cleanTokens,&$allVars,&$assignment,&$remain)
{

	global $T_IGNORE;
	global $T_INCLUDES;
	global $T_ASSIGNMENT;
	global $T_ECHO;
	global $V_ECHO;
	global $V_EXEC;
	global $V_SQL;
	global $V_PREG;
	global $V_INPUT;
	global $senEnd;

	
	
	if (is_array($tokens[$n])) {

			if(in_array($tokens[$n][0],$T_IGNORE)){	
				// return 'T_IGNORE';
				return '';
			}elseif(in_array($tokens[$n][0],$T_INCLUDES)){
				$remain = 1;
				return 'T_INCLUDES';
			// }elseif(in_array($tokens[$n][0],$T_ASSIGNMENT)){
				// return 'T_ASSIGNMENT';
			}elseif(in_array($tokens[$n][0],$T_ECHO)){
				$remain = 1;
				return 'T_ECHO';
			}elseif(in_array($tokens[$n][1],$V_ECHO)){
				$remain = 1;
				return 'T_ECHO';
			}elseif(in_array($tokens[$n][1],$V_EXEC)){
				$remain = 1;
				return 'T_EXEC';
			}elseif(in_array($tokens[$n][1],$V_SQL)){
				$remain = 1;
				return 'T_SQL';
			}elseif(in_array($tokens[$n][1],$V_PREG)){
				$remain = 1;
				return 'T_PREG';
			}elseif(in_array($tokens[$n][1],$V_INPUT)){
				return 'T_INPUT';
				
			}elseif(in_array($tokens[$n][0],$T_ASSIGNMENT)){
				$assignment = 1;
				$T_OP = token_name($tokens[$n][0]);
				$operation = $tokens[$n][1];
				// $varValue = array_pop($cleanTokens);
				$varValue = end($cleanTokens);
				$varName = array_search($varValue,$allVars);
				// var_dump($varValue);
				$t = '';
				// $n++;
				// while(is_array($tokens[$n]) || $tokens[$n]!=';')
				
				do{
					$n++;
					$t .= xxxasdfasdfsdfs_oneTokenize($n,$tokens,$cleanTokens,$allVars,$assignment,$remain).' ';
					
				}while(!$senEnd
					&& (is_array($tokens[$n])
					|| !isSenEnd(substr($tokens[$n],-1,1)))
					);
				
				$assignment = 0;
				// eval('$varValue'.$operation.'$t;');
				// $varValue = substr($varValue,0,-1);
				$allVars[$varName] =  $varValue.$T_OP.$t;
				return $T_OP.$t.' ;';
				
			}elseif(
			// remain system function
			(function_exists($tokens[$n][1]))	
			// remain user-defined function
			|| (T_STRING == $tokens[$n][0])
			){			
				$remain = 1;
				return $tokens[$n][1];
			}elseif(// remain INT				
			(T_LNUMBER == $tokens[$n][0])){
				return $tokens[$n][1];
			}elseif(T_CONSTANT_ENCAPSED_STRING == $tokens[$n][0]){
				$tokenvalue = trim($tokens[$n][1]);
				$v = 'T_CONSTANT_ENCAPSED_STRING';
				$spChar = array('"','\'');
				// is special symbols?
				$startChar = substr($tokenvalue,1,1);
				$endChar = substr($tokenvalue,-2,1);
				if(in_array($startChar,$spChar))
				{
					$v = $startChar.' '.$v;
				}elseif(in_array($endChar,$spChar))
				{
					$v = $v.' '.$endChar;
				}else{
				}
				return $v;
				
			}elseif(T_ENCAPSED_AND_WHITESPACE == $tokens[$n][0]){
				$v = 'T_CONSTANT_ENCAPSED_STRING';
				// var_dump($allVars);
				global $varValue;
				if('"' == substr($varValue,-2,1)
				|| "'" == substr($varValue,-2,1))
				{
					$varValue = substr($varValue,0,-2);
				}

				$tokenvalue = trim($tokens[$n][1]);
				$spChar = array('"','\'');
				$startChar = substr($tokenvalue,0,1);
				$endChar = substr($tokenvalue,-1,1);
				if(in_array($startChar,$spChar))
				{
					$v = $startChar.' '.$v;
				}elseif(in_array($endChar,$spChar))
				{
					$v = $v.' '.$endChar;
				}else{
				}
				
				try{
					if('"' == $tokens[$n+1]
					|| "'" == $tokens[$n+1])
					{
						$n++;
					}
				}catch(Exception $e){}
				return $v;
				
			}elseif(T_VARIABLE == $tokens[$n][0]){
				//deal array
				$varName = $tokens[$n][1];
				
				if('[' == $tokens[$n+1]){
					$varName .= '[';
					$t = $n+1;
					$n += 2;
					while(']'!=$tokens[$n]){
						$varName .= xxxasdfasdfsdfs_oneTokenize($n,$tokens,$cleanTokens,$allVars,$assignment,$remain);
						$n++;
					}
				
					$varName .= ']';
					
					if(1 == $n-$t){
						$i = 0;
						
						while(array_key_exists((substr($varName,0,-1).$i.']'),$allVars))
						{
							$i++;
						}
						$varName = substr($varName,0,-1).$i.']';
					}
				
		
				}
				
				if(array_key_exists($varName,$allVars))
				{
					return $allVars[$varName];
				}elseif($assignment)
				{
					$allVars[$varName] = 'T_UNDEFINED_VAR';
					return 'T_UNDEFINED_VAR';
					
				}else{
					return $varName;
				}
											
			}else{
				return token_name($tokens[$n][0]);
			}
		}elseif('=' == $tokens[$n]){
			
			$assignment = 1;
	
			// $varName = array_pop($cleanTokens);
			$varName = end($cleanTokens);
			
			$t = array_search($varName,$allVars);
			if($t)
			{
				$varName = $t;
			}else{
				
			}
			// var_dump($cleanTokens);
			global $varValue;
			$varValue = '';
			$n++;
			// while(!$senEnd || !isSenEnd($tokens[$n]))
			while(!$senEnd && !isSenEnd($tokens[$n]))
			{
				$varValue .= xxxasdfasdfsdfs_oneTokenize($n,$tokens,$cleanTokens,$allVars,$assignment,$remain).' ';
				$n++;
			}
			// $varValue = substr($varValue,0,-1);
			$assignment = 0;
			$allVars[$varName] =  $varValue;
	
			return $varValue.' '.$tokens[$n];
	
		}else{
			return $tokens[$n];
		}
		
}	
	
function xxxasdfasdfsdfs_tokenize($data)
{
	global $senEnd;
	$tokens = token_get_all($data);
	// var_dump($tokens);
	$cleanTokens = array();

	$allVars = array();
	// global $allVars;
	$tokenNum = count($tokens);
	$assignment = 0;
	$remain = 0;
	for($n=0;$n<$tokenNum;$n++)
	{
		$token = xxxasdfasdfsdfs_oneTokenize($n,$tokens,$oneLine,$allVars,$assignment,$remain);

		// var_dump($token);
		if($token){
			if(!$senEnd
			&& !isSenEnd(substr($token,-1,1)) ){
				$oneLine[] = $token;
				if(1 == $remain){
					// array_filter($oneLine);
					$cleanTokens[] = $token;
				}
			}elseif($remain){
				$oneLine[] = $token;
				// array_filter($oneLine);
				// $cleanTokens = array_merge($cleanTokens,$oneLine);
				$cleanTokens[] = $token;
				$senEnd = 0;
				$remain = 0;
			}else{
				$senEnd = 0;
				$oneLine = array();
			}
		}
	
				
		
	}	
	// var_dump( $allVars);

	$cleanTokens = implode(' ',$cleanTokens);
	$cleanTokens = preg_replace('/[\s]+/',' ',$cleanTokens);
	return $cleanTokens;
}



function xxxasdfasdfsdfs_getAllTokens($path,$dstFile)
{
	$log = __DIR__."\\log.txt";
	$tokens = "";
	// Global $parser;
	
	$files = scandir($path);
	// remove ./ and ../
	unset($files[0]);
	unset($files[1]);
	

	foreach ($files as $file)
	{

		preg_match('/unsafe/',$path,$isUnsafe);
		if(!empty($isUnsafe))
		{			
			preg_match("/CWE_(\d+)_/",$file,$label);
			$label = 'CWE-'.$label[1]."\r\n";
			file_put_contents('./unsafe_y.txt',$label,FILE_APPEND);
		}
	
		$data = file_get_contents($path.$file);
		
		// echo $file."\n\r";
		// file_put_contents($log,$path.$file."\n\r",FILE_APPEND);
		
		$t = xxxasdfasdfsdfs_tokenize($data);
		
			$tokens .= $t;
			$tokens .= "\r\n";
		
	}
	
	file_put_contents($dstFile,$tokens);
}

function xxxasdfasdfsdfs_test()
{
	$data = <<<'END'
<!-- 
Safe sample
input : get the $_GET['userData'] in an array
sanitize : cast in float
File : use of untrusted data in a simple quote attribute
-->

<!DOCTYPE html>
<html>
<head/>
<body>
<?php
$array = array();
$array[] = 'safe' ;
$array[] = $_GET['userData'] ;
$array[] = 'safe' ;
$tainted = $array[1] ;

$tainted = (float) $tainted ;


echo "<div id='".  $tainted ."'>content</div>" ;
?>
<h1>Hello World!</h1>
</body>
</html>
END;

	print(xxxasdfasdfsdfs_tokenize($data));
}







