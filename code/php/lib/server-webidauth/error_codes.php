<?php

class StatusCode
{
	
	const AUTH_SUCCESS = 0;

	// 2x invalid certificate
	// const INVALID_CERTIFICATE = 2;
	const CERT_NOT_PASSED = 21;
	//21 cert malformed, not readable
	//23 SAN field not set
	//24 malformed url in SAN
	
	// 3x WebId
	const WEBID_NOT_LOADABLE = 31
	// 32 not parseable + parse error
	// 33 no key found
	// 34 bad key
	
	// 4x Auth error
	// 41 keys don't match


	const msg = array (
		StatusCode::CERT_NOT_PASSED => "[".StatusCode::CERT_NOT_PASSED."] Client has not passed a certificate\n"
		);
	
}

//echo StatusCode::msg[StatusCode::CERT_NOT_PASSED];

