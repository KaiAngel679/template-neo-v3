<?php

namespace xPaw\SourceQuery\Exception;

class AuthenticationException extends SourceQueryException
{
	const BAD_PASSWORD = 1;
	const BANNED = 2;
}
