<?php

class WebIdAuthStatus
{

	const AUTH_SUCCESS = 0;

	// Starting with 2x invalid certificate
	//const INVALID_CERTIFICATE = 2;
	const CERT_NOT_PASSED = 21;
	const CERT_SAN_NOT_SET = 22;
	const CERT_MALFORMED_URL = 23;

	// Starting with 3x invalid webid
	const WEBID_NOT_LOADABLE = 31;
	const WEBID_NO_RSA_KEYS = 32;
	const WEBID_MALFORMED_RSA_KEY = 33;

	// 4x auth failed
	const AUTH_FAILED = 41;

	const msg = array (
		WebIdAuthStatus::CERT_NOT_PASSED => "[".WebIdAuthStatus::CERT_NOT_PASSED."] Client has not passed a certificate\n",
		WebIdAuthStatus::CERT_SAN_NOT_SET => "[".WebIdAuthStatus::CERT_SAN_NOT_SET."] Client certificate SAN field is not set\n",
		WebIdAuthStatus::CERT_MALFORMED_URL => "[".WebIdAuthStatus::CERT_MALFORMED_URL."] SAN field contains a malformed URL\n",
		WebIdAuthStatus::WEBID_NOT_LOADABLE => "[".WebIdAuthStatus::WEBID_NOT_LOADABLE."] Unable to load WebId at passed URL\n",
		WebIdAuthStatus::WEBID_NO_RSA_KEYS => "[".WebIdAuthStatus::WEBID_NO_RSA_KEYS."] WebId document does not contain any RSA keys\n",
		WebIdAuthStatus::WEBID_MALFORMED_RSA_KEY => "[".WebIdAuthStatus::WEBID_MALFORMED_RSA_KEY."] WebId document contains malformed RSA key\n",
		WebIdAuthStatus::AUTH_FAILED => "[".WebIdAuthStatus::AUTH_FAILED."] Auth failed. No matching key found in WebId document\n"

		);

}
