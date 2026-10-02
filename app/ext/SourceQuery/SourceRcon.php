<?php

namespace xPaw\SourceQuery;

use xPaw\SourceQuery\Exception\AuthenticationException;
use xPaw\SourceQuery\Exception\InvalidPacketException;
use xPaw\SourceQuery\Exception\SocketException;

class SourceRcon
{
	private $Socket;

	private $RconSocket;
	private $RconRequestId = 0;

	public function __construct( $Socket )
	{
		$this->Socket = $Socket;
	}

	public function Close( )
	{
		if( $this->RconSocket )
		{
			FClose( $this->RconSocket );

			$this->RconSocket = null;
		}

		$this->RconRequestId = 0;
	}

	public function Open( )
	{
		if( !$this->RconSocket )
		{
			$this->RconSocket = @FSockOpen( $this->Socket->Address, $this->Socket->Port, $ErrNo, $ErrStr, $this->Socket->Timeout );

			if( $ErrNo || !$this->RconSocket )
			{
				throw new SocketException( 'Can\'t connect to RCON server: ' . $ErrStr, SocketException::CONNECTION_FAILED );
			}

			Stream_Set_Timeout( $this->RconSocket, $this->Socket->Timeout );
			Stream_Set_Blocking( $this->RconSocket, true );
		}
	}

	public function Write( $Header, $String = '' )
	{
		$Command = Pack( 'VV', ++$this->RconRequestId, $Header ) . $String . "\x00\x00";

		$Command = Pack( 'V', StrLen( $Command ) ) . $Command;
		$Length  = StrLen( $Command );

		return $Length === FWrite( $this->RconSocket, $Command, $Length );
	}

	public function Read( )
	{
		$Buffer = new Buffer( );
		$Buffer->Set( FRead( $this->RconSocket, 4 ) );

		if( $Buffer->Remaining( ) < 4 )
		{
			throw new InvalidPacketException( 'Rcon read: Failed to read any data from socket', InvalidPacketException::BUFFER_EMPTY );
		}

		$PacketSize = $Buffer->GetLong( );

		$Buffer->Set( FRead( $this->RconSocket, $PacketSize ) );

		$Data = $Buffer->Get( );

		$Remaining = $PacketSize - StrLen( $Data );

		while( $Remaining > 0 )
		{
			$Data2 = FRead( $this->RconSocket, $Remaining );

			$PacketSize = StrLen( $Data2 );

			if( $PacketSize === 0 )
			{
				throw new InvalidPacketException( 'Read ' . strlen( $Data ) . ' bytes from socket, ' . $Remaining . ' remaining', InvalidPacketException::BUFFER_EMPTY );

				break;
			}

			$Data .= $Data2;
			$Remaining -= $PacketSize;
		}

		$Buffer->Set( $Data );

		return $Buffer;
	}

	public function Command( $Command )
	{
		$this->Write( SourceQuery::SERVERDATA_EXECCOMMAND, $Command );
		$Buffer = $this->Read( );

		$Buffer->GetLong( ); 

		$Type = $Buffer->GetLong( );

		if( $Type === SourceQuery::SERVERDATA_AUTH_RESPONSE )
		{
			throw new AuthenticationException( 'Bad rcon_password.', AuthenticationException::BAD_PASSWORD );
		}
		else if( $Type !== SourceQuery::SERVERDATA_RESPONSE_VALUE )
		{
			throw new InvalidPacketException( 'Invalid rcon response.', InvalidPacketException::PACKET_HEADER_MISMATCH );
		}

		$Data = $Buffer->Get( );

		if( StrLen( $Data ) >= 4000 )
		{
			$this->Write( SourceQuery::SERVERDATA_RESPONSE_VALUE );

			do
			{
				$Buffer = $this->Read( );

				$Buffer->GetLong( ); 

				if( $Buffer->GetLong( ) !== SourceQuery::SERVERDATA_RESPONSE_VALUE )
				{
					break;
				}

				$Data2 = $Buffer->Get( );

				if( $Data2 === "\x00\x01\x00\x00\x00\x00" )
				{
					break;
				}

				$Data .= $Data2;
			}
			while( true );
		}

		return rtrim( $Data, "\0" );
	}

	public function Authorize( $Password )
	{
		$this->Write( SourceQuery::SERVERDATA_AUTH, $Password );
		$Buffer = $this->Read( );

		$RequestID = $Buffer->GetLong( );
		$Type      = $Buffer->GetLong( );

		if( $Type === SourceQuery::SERVERDATA_RESPONSE_VALUE )
		{
			$Buffer = $this->Read( );

			$RequestID = $Buffer->GetLong( );
			$Type      = $Buffer->GetLong( );
		}

		if( $RequestID === -1 || $Type !== SourceQuery::SERVERDATA_AUTH_RESPONSE )
		{
			throw new AuthenticationException( 'RCON authorization failed.', AuthenticationException::BAD_PASSWORD );
		}
	}
}