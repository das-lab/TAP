<?php


// tokens to ignore while scanning
$T_IGNORE = array(
	// T_BAD_CHARACTER,
	T_DOC_COMMENT,
	T_COMMENT,
	// T_ML_COMMENT,
	T_INLINE_HTML,
	T_WHITESPACE,
	T_OPEN_TAG,
	T_CLOSE_TAG
);
	

	
// variable assignment tokens
$T_ASSIGNMENT = array(
	T_AND_EQUAL,
	T_CONCAT_EQUAL,
	T_DIV_EQUAL,
	T_MINUS_EQUAL,
	T_MOD_EQUAL,
	T_MUL_EQUAL,
	T_OR_EQUAL,
	T_PLUS_EQUAL,
	T_SL_EQUAL,
	T_SR_EQUAL,
	T_XOR_EQUAL
);
	

	
// condition operators
// 暂不用
$T_COMPARISON = array(
	T_IS_EQUAL,
	T_IS_GREATER_OR_EQUAL,
	T_IS_IDENTICAL,
	T_IS_NOT_EQUAL,
	T_IS_NOT_IDENTICAL,
	T_IS_SMALLER_OR_EQUAL
);
	

	
// including operation tokens
$T_INCLUDES = array(
	T_INCLUDE,
	T_INCLUDE_ONCE,
	T_REQUIRE,
	T_REQUIRE_ONCE
);
	
// XSS affected operation tokens
$T_ECHO = array(
	T_PRINT,
	T_ECHO,
	T_OPEN_TAG_WITH_ECHO,
	T_EXIT
);
		
$V_ECHO = array(
	'print_r',
	'printf',
	'vprintf',
	'trigger_error',
	'user_error',
	'odbc_result_all',
	'ifx_htmltbl_result'
);
	

$V_INPUT = array(
		'$_GET',
		'$_POST',
		'$_COOKIE',
		'$_REQUEST',
		'$_FILES',
		'$_SERVER',
		'$HTTP_GET_VARS',
		'$HTTP_POST_VARS',
		'$HTTP_COOKIE_VARS',  
		'$HTTP_REQUEST_VARS', 
		'$HTTP_POST_FILES',
		'$HTTP_SERVER_VARS',
		'$HTTP_RAW_POST_DATA',
		'$argc',
		'$argv',
		'get_headers',
		'getallheaders',
		'get_browser',
		#'getenv',
		#'gethostbyaddr',
		// 'runkit_superglobals',
		'import_request_variables'
	);
	
$V_PREG = array(
		'preg_filter',
		'preg_grep',
		'preg_last_error',
		'preg_match_all',
		'preg_match',
		'preg_quote',
		'preg_replace_callback',
		'preg_replace',
		'ereg_replace',
		'ereg',
		'eregi_replace',
		'eregi',
	);
	
$V_EXEC = array(
		'backticks',
		'exec',
		'expect_popen',
		'passthru',
		'pcntl_exec',
		'popen',
		'proc_open',
		'shell_exec',
		'system',
		'eval',
		);

$V_SQL = array(
	// Abstraction Layers
	// 'dba_open',
	// 'dba_popen', 
	'dba_insert',
	'dba_fetch', 
	'dba_delete', 
	'dbx_query', 
	'odbc_do',
	'odbc_exec',
	'odbc_execute',
	'db2_exec' ,
	'db2_execute',
	'fbsql_db_query',
	'fbsql_query', 
	'ibase_query', 
	'ibase_execute', 
	'ifx_query', 
	'ifx_do',
	'ingres_query',
	'ingres_execute',
	'ingres_unbuffered_query',
	'msql_db_query', 
	'msql_query',
	// 'msql', 
	'mssql_query', 
	'mssql_execute',
	'mysql_db_query',  
	'mysql_query', 
	'mysql_unbuffered_query', 
	'mysqli_stmt_execute',
	'mysqli_query',
	'mysqli_real_query',
	'mysqli_master_query',
	'oci_execute',
	'ociexecute',
	'ovrimos_exec',
	'ovrimos_execute',
	'ora_do', 
	'ora_exec', 
	'pg_query',
	'pg_send_query',
	'pg_send_query_params',
	'pg_send_prepare',
	'pg_prepare',
	'sqlite_open',
	'sqlite_popen',
	'sqlite_array_query',
	'arrayQuery',
	'singleQuery',
	'sqlite_query',
	'sqlite_exec',
	'sqlite_single_query',
	'sqlite_unbuffered_query',
	'sybase_query', 
	'sybase_unbuffered_query'
);

?>	