<?php

namespace app\modules\module_block_main_servers\ext;

class Buffer
{
    private string $Buffer;
    private int $Length;
    private int $Position;

    public function Set(string $Buffer): void
    {
        $this->Buffer   = $Buffer;
        $this->Length   = strlen($Buffer);
        $this->Position = 0;
    }

    public function Remaining(): int
    {
        return $this->Length - $this->Position;
    }

    public function Get(int $Length = -1): string
    {
        if ($Length === 0) return '';
        $Remaining = $this->Remaining();
        if ($Length === -1) $Length = $Remaining;
        else if ($Length > $Remaining) return '';
        $Data = substr($this->Buffer, $this->Position, $Length);
        $this->Position += $Length;
        return $Data;
    }

    public function GetByte(): int
    {
        return ord($this->Get(1));
    }

    public function GetShort(): int
    {
        if ($this->Remaining() < 2) die('Not enough data to unpack a short.');
        $Data = unpack('v', $this->Get(2));
        return $Data[1] ?? 0;
    }

    public function GetLong(): int
    {
        if ($this->Remaining() < 4) die('Not enough data to unpack a long.');
        $Data = unpack('l', $this->Get(4));
        return $Data[1] ?? 0;
    }

    public function GetFloat(): float
    {
        if ($this->Remaining() < 4) die('Not enough data to unpack a float.');
        $Data = unpack('f', $this->Get(4));
        return $Data[1] ?? 0.0;
    }

    public function GetUnsignedLong(): int
    {
        if ($this->Remaining() < 4) die('Not enough data to unpack an unsigned long.');
        $Data = unpack('V', $this->Get(4));
        return $Data[1] ?? 0;
    }

    public function GetString(): string
    {
        $ZeroBytePosition = strpos($this->Buffer, "\0", $this->Position);
        if ($ZeroBytePosition === false) return '';
        $String = $this->Get($ZeroBytePosition - $this->Position);
        $this->Position++;
        return $String;
    }
}

class Rcon
{
    private string $Address;
    private int $Port;

    private $RconSocket;
    private int $RconRequestId;
    private string $lastError;
    private int $maxPacketSize = 65536;
    private int $maxTotalResponse = 4194304;

    public function __construct(string $Address, int $Port)
    {
        $this->Address = $Address;
        $this->Port = $Port;
        $this->RconSocket = null;
        $this->RconRequestId = 0;
        $this->lastError = '';
    }

    public function Disconnect(): void
    {
        if ($this->RconSocket) {
            fclose($this->RconSocket);
            $this->RconSocket = null;
        }
        $this->RconRequestId = 0;
    }

    public function Connect(): bool
    {
        if (!$this->RconSocket) {
            $this->RconSocket = @fsockopen($this->Address, $this->Port, $ErrNo, $ErrStr, 1.5);
            if ($ErrNo || !$this->RconSocket) {
                $this->lastError = 'Connect failed: ' . ($ErrStr ?: 'unknown error') . ' (#' . $ErrNo . ')';
                return false;
            }
            stream_set_timeout($this->RconSocket, 3);
            stream_set_blocking($this->RconSocket, true);
        }
        return true;
    }

    private function Write(int $Header, string $String = ''): bool
    {
        if (!$this->RconSocket || !is_resource($this->RconSocket)) {
            $this->lastError = 'Socket is not connected';
            return false;
        }

        $Command = pack('VV', ++$this->RconRequestId, $Header) . $String . "\x00\x00";

        $Command = pack('V', strlen($Command)) . $Command;
        $Length  = strlen($Command);

        $Written = @fwrite($this->RconSocket, $Command, $Length);
        
        if ($Written === false || $Written !== $Length) {
            $this->lastError = 'Write failed: connection lost or incomplete write';
            return false;
        }
        
        return true;
    }

    private function Read(): ?Buffer
    {
        $Buffer = new Buffer();
        $Buffer->Set(fread($this->RconSocket, 4));

        if ($Buffer->Remaining() < 4) return null;

        $PacketSize = $Buffer->GetLong();

        if ($PacketSize <= 0 || $PacketSize > $this->maxPacketSize) {
            $this->lastError = 'Packet size out of bounds: ' . (int)$PacketSize;
            return null;
        }

        $Buffer->Set(fread($this->RconSocket, $PacketSize));

        $Data = $Buffer->Get();
        $Remaining = $PacketSize - strlen($Data);

        while ($Remaining > 0) {
            $Data2 = fread($this->RconSocket, $Remaining);
            $PacketSize = strlen($Data2);

            if ($PacketSize === 0) return null;

            $Data .= $Data2;
            $Remaining -= $PacketSize;
        }

        $Buffer->Set($Data);
        return $Buffer;
    }

    public function Command(string $Command): ?string
    {
        if (!$this->Write(2, $Command)) {
            $this->lastError = 'Write failed (command)';
            return null;
        }

        $Buffer = $this->Read();
        if (!$Buffer) {
            $this->lastError = 'Read failed (initial response)';
            return null;
        }
        if ($Buffer->Remaining() < 8) {
            $this->lastError = 'Malformed packet (<8 bytes)';
            return null;
        }
        $Buffer->GetLong();
        $Type = $Buffer->GetLong();

        if ($Type === 2) {
            return null;
        }
        if ($Type !== 0) {
            return null;
        }

        $Data = $Buffer->Get();

        if (strlen($Data) > $this->maxTotalResponse) {
            $this->lastError = 'Response too large (initial chunk)';
            return null;
        }

        if (strlen($Data) >= 4000) {
            do {
                if (!$this->Write(0)) {
                    $this->lastError = 'Write failed (multi-part)';
                    break;
                }
                $Buffer = $this->Read();
                if (!$Buffer) {
                    $this->lastError = 'Read failed (multi-part)';
                    break;
                }
                if ($Buffer->Remaining() < 4) {
                    $this->lastError = 'Malformed packet (multi-part header)';
                    break;
                }
                if ($Buffer->GetLong() !== 0) break;

                $Data2 = $Buffer->Get();
                if ($Data2 === "\x00\x01\x00\x00\x00\x00") break;

                if (strlen($Data) + strlen($Data2) > $this->maxTotalResponse) {
                    $this->lastError = 'Response too large (multi-part total)';
                    break;
                }
                $Data .= $Data2;
            } while (true);
        }
        return rtrim($Data, "\0");
    }

    public function RconPass(string $Password): bool
    {
        if (!$this->Write(3, $Password)) {
            $this->lastError = 'Write failed (auth)';
            return false;
        }
        $Buffer = $this->Read();
        if (!$Buffer) {
            $this->lastError = 'Read failed (auth)';
            return false;
        }
        if ($Buffer->Remaining() < 8) {
            $this->lastError = 'Malformed packet (auth response)';
            return false;
        }
        $RequestID = $Buffer->GetLong();
        $Type      = $Buffer->GetLong();
        if ($Type === 0) {
            $Buffer = $this->Read();
            if ($Buffer && $Buffer->Remaining() >= 8) {
                $RequestID = $Buffer->GetLong();
                $Type      = $Buffer->GetLong();
            }
        }

        return $RequestID !== -1 && $Type === 2;
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }
}
