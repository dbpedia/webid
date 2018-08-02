<?php

class StatusCode
{
	
	const AUTH_SUCCESS = 0;

	// Starting with 2x invalid certificate
	//const INVALID_CERTIFICATE = 2;
	const CERT_NOT_PASSED = 21;

	const msg = array (
		StatusCode::CERT_NOT_PASSED => "[".StatusCode::CERT_NOT_PASSED."] Client has not passed a certificate\n"
		);
	
}

echo StatusCode::msg[StatusCode::CERT_NOT_PASSED];

