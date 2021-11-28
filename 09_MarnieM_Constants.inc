<?php
// MarnieM Constants

	define( 'MAPPIX_PER_METER',   0.851 );
	define( 'METERS_PER_MAPPIX',  1.175 );

	define( 'LOGINID_MINLEN',   6 );
	define( 'LOGINID_MAXLEN',   15 );
	define( 'PWD_MINLEN',       8 );
	define( 'PWD_MAXLEN',       25 );
	define( 'PHONE_MINLEN',     8 );
	define( 'PHONE_MAXLEN',     25 );
	define( 'BSNSNUM_MINVAL',   1 );
	define( 'BSNSNUM_MAXVAL',   999 );
	define( 'ADMIN_LOGIN_ID', 99999 );
  define( 'CALL_FOR_PRICE', 123459876 );

//Type of form
	define( 'USER_REG',    1 );
	define( 'USER_PROF',   2 );
	define( 'USER_CHKOUT', 3 );
	define( 'BSNS_REG',    11 );
	define( 'BSNS_PROF',   12 );

//Password to upper case
	define( 'PWD_NOT_TO_UPPER', 0 );
	define( 'PWD_TO_UPPER',     1 );


//What to test
	define( 'TEST_LOGIN',   1 ); // Binary  0001
	define( 'TEST_PWD',     2 ); // Binary  0010
	define( 'TEST_BOTH',    3 ); // Binary  0011
	define( 'TEST_NEWPWD',  4 ); // Binary  0100


	define( 'PTBL_MAX_SPEC_LEN',       10 );

	define( 'USE_KPRICE',       true );
	define( 'NO_KPRICE',       false );

//----- Safe Value Types -----
	define( 'SV_IDCODE',  1 );    // 0-9,A-Z,-
	define( 'SV_INT',      3 );   // intval( $origValue )
	define( 'SV_LOGINID',  4 );   // [A-Za-z0-9_] Alphanumeric characters plus "_", Checked OK, sample: Doug12_R _Rogers
	define( 'SV_STRING',   5 );   // htmlspecialchars( $origValue, ENT_QUOTES )
	define( 'SV_ALPHANUM', 6 );   // Letters and numbers and _ only
	define( 'SV_ALPHA',    7 );   // Letters and _ only, no numbers
	define( 'SV_PHONE',    8 );   // Only allow digits, space,(,),+,-
	define( 'SV_EMAIL',    9 );   // [^a-zA-Z0-9_\-\+\.@\/]
	define( 'SV_EMWEB',   10 );   // [^a-zA-Z0-9_\-\+\.@\/]
	define( 'SV_WEBADDR', 11 );   // [^a-zA-Z0-9_\-\+\.@\/]
	define( 'SV_DATE',    13 );   // Only allow digits, -  eg:  14-03-27
	define( 'SV_BSNSID',  15 );   // Only allow digits, decimal point (.), and ; as list separator
	define( 'SV_NICKNAME',  16 );   //Letters and numbers and _ . - only
	define( 'SV_STRINGLIST',  17 ); // [^a-zA-Z, ]


//----- Safe Value Types -----
	define( 'CV_LOGINID', 1 );
	define( 'CV_PWD',     2 );
	define( 'CV_PHONE',   3 );
	define( 'CV_EMAIL',   4 );
	define( 'CV_EMWEB',   5 );
	define( 'CV_BSNSNUM', 6 );
	define( 'CV_NICKNAME', 7 );


//User fields
	define( 'C_NUM',     0 );
	define( 'C_NFAM',    1 );
	define( 'C_NOTHR',   2 );
	define( 'C_CMPNY',   3 );
	define( 'C_ADSTR',   4 );
	define( 'C_AD',      5 );
	define( 'C_BASPRC',  6 );
	define( 'C_BRKPRC',  7 );
	define( 'C_NUMAVL',  8 );
	define( 'C_BODRED',  9 );
	define( 'C_BODATE', 10 );

