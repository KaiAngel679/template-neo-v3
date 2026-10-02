<?php

declare(strict_types=1);

namespace app\modules\module_page_search\ext;

class SteamidConverter
{
	private static $AccountTypeChars =
	[
		self::TypeAnonGameServer => 'A',
		self::TypeGameServer     => 'G',
		self::TypeMultiseat      => 'M',
		self::TypePending        => 'P',
		self::TypeContentServer  => 'C',
		self::TypeClan           => 'g',
		self::TypeChat           => 'T',
		self::TypeInvalid        => 'I',
		self::TypeIndividual     => 'U',
		self::TypeAnonUser       => 'a',
	];

	private static $SteamInviteDictionary =
	[
		'0' => 'b',
		'1' => 'c',
		'2' => 'd',
		'3' => 'f',
		'4' => 'g',
		'5' => 'h',
		'6' => 'j',
		'7' => 'k',
		'8' => 'm',
		'9' => 'n',
		'a' => 'p',
		'b' => 'q',
		'c' => 'r',
		'd' => 't',
		'e' => 'v',
		'f' => 'w',
	];

	const UniverseInvalid  = 0;
	const UniversePublic   = 1;
	const UniverseBeta     = 2;
	const UniverseInternal = 3;
	const UniverseDev      = 4;

	const TypeInvalid        = 0;
	const TypeIndividual     = 1;
	const TypeMultiseat      = 2;
	const TypeGameServer     = 3;
	const TypeAnonGameServer = 4;
	const TypePending        = 5;
	const TypeContentServer  = 6;
	const TypeClan           = 7;
	const TypeChat           = 8;
	const TypeP2PSuperSeeder = 9;
	const TypeAnonUser       = 10;

	const AllInstances    = 0;
	const DesktopInstance = 1;
	const ConsoleInstance = 2;
	const WebInstance     = 4;

	const InstanceFlagClan     = 524288;
	const InstanceFlagLobby    = 262144;
	const InstanceFlagMMSLobby = 131072;

	const VanityIndividual = 1;
	const VanityGroup      = 2;
	const VanityGameGroup  = 3;

	private $Data;

	public function __construct($Value = null)
	{
		$this->Data = gmp_init(0);

		if ($Value === null) {
			return;
		}

		if (preg_match('/^STEAM_([0-4]):([0-1]):([0-9]{1,10})$/', (string)$Value, $Matches) === 1) {
			$AccountID = $Matches[3];

			if (gmp_cmp($AccountID, '4294967295') > 0) {
				throw new \InvalidArgumentException('Provided SteamID exceeds max unsigned 32-bit integer.');
			}

			$Universe = (int)$Matches[1];

			if ($Universe === self::UniverseInvalid) {
				$Universe = self::UniversePublic;
			}

			$AuthServer = (int)$Matches[2];
			$AccountID = ((int)$AccountID << 1) | $AuthServer;

			$this->SetAccountUniverse($Universe);
			$this->SetAccountInstance(self::DesktopInstance);
			$this->SetAccountType(self::TypeIndividual);
			$this->SetAccountID($AccountID);
		} else if (preg_match('/^\\[([AGMPCgcLTIUai]):([0-4]):([0-9]{1,10})(:([0-9]+))?\\]$/', (string)$Value, $Matches) === 1) {
			$AccountID = $Matches[3];

			if (gmp_cmp($AccountID, '4294967295') > 0) {
				throw new \InvalidArgumentException('Provided SteamID exceeds max unsigned 32-bit integer.');
			}

			$Type = $Matches[1];

			if ($Type === 'i') {
				$Type = 'I';
			}

			if ($Type === 'T' || $Type === 'g') {
				$InstanceID = self::AllInstances;
			} else if (isset($Matches[5])) {
				$InstanceID = (int)$Matches[5];
			} else if ($Type === 'U') {
				$InstanceID = self::DesktopInstance;
			} else {
				$InstanceID = self::AllInstances;
			}

			if ($Type === 'c') {
				$InstanceID = self::InstanceFlagClan;

				$this->SetAccountType(self::TypeChat);
			} else if ($Type === 'L') {
				$InstanceID = self::InstanceFlagLobby;

				$this->SetAccountType(self::TypeChat);
			} else {
				$this->SetAccountType(array_search($Type, self::$AccountTypeChars, true));
			}

			$this->SetAccountUniverse((int)$Matches[2]);
			$this->SetAccountInstance($InstanceID);
			$this->SetAccountID($AccountID);
		} else if (self::IsNumeric($Value)) {
			$this->Data = gmp_init($Value, 10);
		} else {
			throw new \InvalidArgumentException('Provided SteamID is invalid.');
		}
	}

	public function RenderSteam2(): string
	{
		switch ($this->GetAccountType()) {
			case self::TypeInvalid:
			case self::TypeIndividual: {
					$Universe = $this->GetAccountUniverse();
					$AccountID = $this->GetAccountID();

					return 'STEAM_' . $Universe . ':' . ($AccountID & 1) . ':' .
						($AccountID >> 1);
				}
			default: {
					return $this->ConvertToUInt64();
				}
		}
	}

	public function RenderSteam3(): string
	{
		$AccountInstance = $this->GetAccountInstance();
		$AccountType = $this->GetAccountType();
		$AccountTypeChar = isset(self::$AccountTypeChars[$AccountType]) ?
			self::$AccountTypeChars[$AccountType] :
			'i';

		$RenderInstance = false;

		switch ($AccountType) {
			case self::TypeChat: {
					if ($AccountInstance & SteamidConverter::InstanceFlagClan) {
						$AccountTypeChar = 'c';
					} else if ($AccountInstance & SteamidConverter::InstanceFlagLobby) {
						$AccountTypeChar = 'L';
					}

					break;
				}
			case self::TypeAnonGameServer:
			case self::TypeMultiseat: {
					$RenderInstance = true;

					break;
				}
			case self::TypeIndividual: {
					$RenderInstance = $AccountInstance != self::DesktopInstance;

					break;
				}
		}

		$Return = '[' . $AccountTypeChar . ':' . $this->GetAccountUniverse() .
			':' . $this->GetAccountID();

		if ($RenderInstance) {
			$Return .= ':' . $AccountInstance;
		}

		return $Return . ']';
	}

	public function RenderSteamInvite(): string
	{
		switch ($this->GetAccountType()) {
			case self::TypeInvalid:
			case self::TypeIndividual: {
					$Code = dechex($this->GetAccountID());
					$Code = strtr($Code, self::$SteamInviteDictionary);
					$Length = strlen($Code);

					if ($Length > 3) {
						$Code = substr_replace($Code, '-', (int)($Length / 2), 0);
					}

					return $Code;
				}
			default: {
					throw new \InvalidArgumentException('This can only be used on Individual SteamID.');
				}
		}
	}

	public function RenderCsgoFriendCode(): string
	{
		$AccountType = $this->GetAccountType();

		if ($AccountType !== self::TypeInvalid && $AccountType !== self::TypeIndividual) {
			throw new \InvalidArgumentException('This can only be used on Individual SteamID.');
		}

		$Hash = gmp_or($this->GetAccountID(), '0x4353474F00000000');
		$Hash = gmp_export($Hash, 8, GMP_LITTLE_ENDIAN);
		$Hash = md5($Hash, true);
		$Hash = unpack('i', substr($Hash, 0, 4))[1];

		$Result = gmp_init(0);

		for ($i = 0; $i < 8; $i++) {
			$IdNibble = $this->Get(4 * $i, '0xF');
			$HashNibble = gmp_and(self::ShiftRight($Hash, $i), 1);

			$a = gmp_or(self::ShiftLeft($Result, 4), $IdNibble);

			$Result = gmp_or(self::ShiftLeft(self::ShiftRight($Result, 28), 32), $a);
			$Result = gmp_or(
				self::ShiftLeft(self::ShiftRight($Result, 31), 32),
				gmp_or(self::ShiftLeft($a, 1), $HashNibble)
			);
		}

		$Result = gmp_import(gmp_export($Result, 8, GMP_BIG_ENDIAN), 8, GMP_LITTLE_ENDIAN);
		$Base32 = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$FriendCode = '';

		for ($i = 0; $i < 13; $i++) {
			if ($i === 4 || $i === 9) {
				$FriendCode .= '-';
			}

			$FriendCode .= $Base32[(int)gmp_and($Result, 31)];
			$Result = self::ShiftRight($Result, 5);
		}

		return substr($FriendCode, 5);
	}

	public function IsValid(): bool
	{
		$AccountType = $this->GetAccountType();

		if ($AccountType <= self::TypeInvalid || $AccountType > self::TypeAnonUser) {
			return false;
		}

		$AccountUniverse = $this->GetAccountUniverse();

		if ($AccountUniverse <= self::UniverseInvalid || $AccountUniverse > self::UniverseDev) {
			return false;
		}

		$AccountID = $this->GetAccountID();
		$AccountInstance = $this->GetAccountInstance();

		if ($AccountType === self::TypeIndividual) {
			if ($AccountID == 0 || $AccountInstance > self::WebInstance) {
				return false;
			}
		}

		if ($AccountType === self::TypeClan) {
			if ($AccountID == 0 || $AccountInstance != 0) {
				return false;
			}
		}

		if ($AccountType === self::TypeGameServer) {
			if ($AccountID == 0) {
				return false;
			}
		}

		return true;
	}

	public static function SetFromURL(string $Value, callable $VanityCallback): SteamidConverter
	{
		if (preg_match('/^https?:\/\/steamcommunity\.com\/profiles\/(.+?)(?:\/|$)/', $Value, $Matches) === 1) {
			$Value = $Matches[1];
		} else if (
			preg_match('/^https?:\/\/steamcommunity\.com\/(id|groups|games)\/([\w-]+)(?:\/|$)/', $Value, $Matches) === 1
			||       preg_match('/^()([\w-]+)$/', $Value, $Matches) === 1
		) {
			$Length = strlen($Matches[2]);

			if ($Length < 2 || $Length > 32) {
				throw new \InvalidArgumentException('Provided vanity url has bad length.');
			}

			if (self::IsNumeric($Matches[2])) {
				$SteamID = new SteamidConverter($Matches[2]);

				if ($SteamID->IsValid()) {
					return $SteamID;
				}
			}

			switch ($Matches[1]) {
				case 'groups':
					$VanityType = self::VanityGroup;
					break;
				case 'games':
					$VanityType = self::VanityGameGroup;
					break;
				default:
					$VanityType = self::VanityIndividual;
			}

			$Value = call_user_func($VanityCallback, $Matches[2], $VanityType);

			if ($Value === null) {
				throw new \InvalidArgumentException('Provided vanity url does not resolve to any SteamID.');
			}
		} else if (preg_match('/^https?:\/\/(steamcommunity\.com\/user|s\.team\/p)\/([\w-]+)(?:\/|$)/', $Value, $Matches) === 1) {
			$Value = strtolower($Matches[2]);
			$Value = preg_replace('/[^' . implode('', self::$SteamInviteDictionary) . ']/', '', $Value);
			$Value = strtr($Value, array_flip(self::$SteamInviteDictionary));
			$Value = hexdec($Value);

			$Value = '[U:1:' . $Value . ']';
		}

		return new SteamidConverter($Value);
	}

	public function SetFromUInt64($Value): SteamidConverter
	{
		if (self::IsNumeric($Value)) {
			$this->Data = gmp_init($Value, 10);
		} else {
			throw new \InvalidArgumentException('Provided SteamID is not numeric.');
		}

		return $this;
	}

	public function ConvertToUInt64(): string
	{
		return gmp_strval($this->Data);
	}

	public function SetFromCsgoFriendCode($Value): SteamidConverter
	{
		if (!is_string($Value) || strlen($Value) !== 10 || $Value[5] !== '-') {
			throw new \InvalidArgumentException('Given input is not a valid CS:GO friend code.');
		}

		$Value = 'AAAA-' . $Value;
		$Value = str_replace('-', '', $Value);

		$Base32 = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$Result = gmp_init(0);

		for ($i = 0; $i < 13; $i++) {
			$Character = strpos($Base32, $Value[$i]);

			if ($Character === false) {
				throw new \InvalidArgumentException('Given input is malformed.');
			}

			$Result = gmp_or($Result, self::ShiftLeft($Character, 5 * $i));
		}

		$Result = gmp_import(gmp_export($Result, 8, GMP_BIG_ENDIAN), 8, GMP_LITTLE_ENDIAN);
		$AccountId = 0;

		for ($i = 0; $i < 8; $i++) {
			$Result = self::ShiftRight($Result, 1);
			$IdNibble = gmp_and($Result, '0xF');
			$Result = self::ShiftRight($Result, 4);

			$AccountId = gmp_or(self::ShiftLeft($AccountId, 4), $IdNibble);
		}

		$this->SetAccountID($AccountId);
		$this->SetAccountType(self::TypeIndividual);
		$this->SetAccountUniverse(self::UniversePublic);
		$this->SetAccountInstance(1);

		return $this;
	}

	public function GetAccountID(): int
	{
		return gmp_intval($this->Get(0, '4294967295'));
	}

	public function GetAccountInstance(): int
	{
		return gmp_intval($this->Get(32, '1048575'));
	}

	public function GetAccountType(): int
	{
		return gmp_intval($this->Get(52, '15'));
	}

	public function GetAccountUniverse(): int
	{
		return gmp_intval($this->Get(56, '255'));
	}

	public function SetAccountID($Value): SteamidConverter
	{
		$this->Set(0, '4294967295', $Value);

		return $this;
	}

	public function SetAccountInstance(int $Value): SteamidConverter
	{
		$this->Set(32, '1048575', $Value);

		return $this;
	}

	public function SetAccountType(int $Value): SteamidConverter
	{
		$this->Set(52, '15', $Value);

		return $this;
	}

	public function SetAccountUniverse($Value): SteamidConverter
	{
		$this->Set(56, '255', $Value);

		return $this;
	}

	private function Get(int $BitOffset, $ValueMask): \GMP
	{
		return gmp_and(self::ShiftRight($this->Data, $BitOffset), $ValueMask);
	}

	private function Set(int $BitOffset, $ValueMask, $Value): void
	{
		$this->Data = gmp_or(
			gmp_and($this->Data, gmp_com(self::ShiftLeft($ValueMask, $BitOffset))),
			self::ShiftLeft(gmp_and($Value, $ValueMask), $BitOffset)
		);
	}

	private static function ShiftLeft($x, int $n): \GMP
	{
		return gmp_mul($x, gmp_pow(2, $n));
	}

	private static function ShiftRight($x, int $n): \GMP
	{
		return gmp_div_q($x, gmp_pow(2, $n));
	}

	private static function IsNumeric($n): bool
	{
		return preg_match('/^[1-9][0-9]{0,19}$/', (string)$n) === 1;
	}
}
