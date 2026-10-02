<?php

namespace xPaw\SourceQuery;

use xPaw\SourceQuery\Exception\InvalidPacketException;

class Buffer
{
	private $Buffer = '';

	private $Length = 0;

	private $Position = 0;

	public function Set( $Buffer )
	{
		$this->Buffer   = $Buffer;
		$this->Length   = StrLen( $Buffer );
		$this->Position = 0;
	}

	public function Remaining( )
	{
		return $this->Length - $this->Position;
	}

	public function Get( $Length = -1 )
	{
		if( $Length === 0 )
		{
			return '';
		}

		$Remaining = $this->Remaining( );

		if( $Length === -1 )
		{
			$Length = $Remaining;
		}
		else if( $Length > $Remaining )
		{
			return '';
		}

		$Data = SubStr( $this->Buffer, $this->Position, $Length );

		$this->Position += $Length;

		return $Data;
	}

	public function GetByte( )
	{
		return Ord( $this->Get( 1 ) );
	}

	public function GetShort( )
	{
		if( $this->Remaining( ) < 2 )
		{
			throw new InvalidPacketException( 'Not enough data to unpack a short.', InvalidPacketException::BUFFER_EMPTY );
		}

		$Data = UnPack( 'v', $this->Get( 2 ) );

		return (int)$Data[ 1 ];
	}

	public function GetLong( )
	{
		if( $this->Remaining( ) < 4 )
		{
			throw new InvalidPacketException( 'Not enough data to unpack a long.', InvalidPacketException::BUFFER_EMPTY );
		}

		$Data = UnPack( 'l', $this->Get( 4 ) );

		return (int)$Data[ 1 ];
	}

	public function GetFloat( )
	{
		if( $this->Remaining( ) < 4 )
		{
			throw new InvalidPacketException( 'Not enough data to unpack a float.', InvalidPacketException::BUFFER_EMPTY );
		}

		$Data = UnPack( 'f', $this->Get( 4 ) );

		return (float)$Data[ 1 ];
	}

	public function GetUnsignedLong( )
	{
		if( $this->Remaining( ) < 4 )
		{
			throw new InvalidPacketException( 'Not enough data to unpack an usigned long.', InvalidPacketException::BUFFER_EMPTY );
		}

		$Data = UnPack( 'V', $this->Get( 4 ) );

		return (int)$Data[ 1 ];
	}

	public function GetString( )
	{
		$ZeroBytePosition = StrPos( $this->Buffer, "\0", $this->Position );

		if( $ZeroBytePosition === false )
		{
			return '';
		}

		$String = $this->Get( $ZeroBytePosition - $this->Position );

		$this->Position++;

		return $String;
	}
}