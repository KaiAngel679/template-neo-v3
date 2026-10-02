<?php

namespace xPaw\SourceQuery\Exception;

class SocketException extends SourceQueryException
{
	const COULD_NOT_CREATE_SOCKET = 1;
	const NOT_CONNECTED = 2;
	const CONNECTION_FAILED = 3;
	const INVALID_ENGINE = 3;
}
