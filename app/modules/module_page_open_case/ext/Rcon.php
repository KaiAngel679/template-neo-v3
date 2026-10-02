<?php
namespace app\modules\module_page_open_case\ext;

    class Buffer
    {
        private $Buffer;
        private $Length;
        private $Position;
        
        public function Set($Buffer)
        {
            $this->Buffer   = $Buffer;
            $this->Length   = StrLen($Buffer);
            $this->Position = 0;
        }
        
        public function Remaining(){
            return $this->Length - $this->Position;
        }
        
        public function Get($Length = -1){
            if($Length === 0) return '';
            $Remaining = $this->Remaining();
            if($Length === -1)$Length = $Remaining;
            else if($Length > $Remaining)return '';
            $Data = SubStr($this->Buffer, $this->Position, $Length);
            $this->Position += $Length;
            return $Data;
        }
        
        public function GetByte(){
            return Ord($this->Get(1));
        }
        
        public function GetShort(){
            if($this->Remaining() < 2)die('Not enough data to unpack a short.');
            $Data = UnPack('v', $this->Get(2));
            return $Data[1];
        }
        
        public function GetLong(){
            if($this->Remaining() < 4)die( 'Not enough data to unpack a long.');  
            $Data = UnPack('l', $this->Get(4));
            return $Data[1];
        }
        
        public function GetFloat(){
            if( $this->Remaining() < 4)die('Not enough data to unpack a float.');
            $Data = UnPack('f', $this->Get(4));
            return $Data[1];
        }
        
        public function GetUnsignedLong(){
            if($this->Remaining() < 4)die('Not enough data to unpack an usigned long.');
            $Data = UnPack('V', $this->Get(4));
            return $Data[1];
        }
        
        public function GetString(){
            $ZeroBytePosition = StrPos($this->Buffer, "\0", $this->Position);
            if($ZeroBytePosition === false)return '';
            $String = $this->Get($ZeroBytePosition - $this->Position);
            $this->Position++;
            return $String;
        }
    }

    class Rcon{
        private $Address;
        private $Port;
        
        private $RconSocket;
        private $RconRequestId;
        
        public function __construct($Address, $Port)
        {
            $this->Address = $Address;
            $this->Port = $Port;
        }
        
        public function Disconnect(){
            if($this->RconSocket){
                FClose($this->RconSocket);
                $this->RconSocket = null;
            }
            $this->RconRequestId = 0;
        }
        
        public function Connect(){
            if(!$this->RconSocket){
                $this->RconSocket = @FSockOpen($this->Address, $this->Port, $ErrNo, $ErrStr, 3);
                if($ErrNo || !$this->RconSocket)return false;
                Stream_Set_Timeout($this->RconSocket, 3);
                Stream_Set_Blocking($this->RconSocket, true);
            }
            return true;
        }
        
        private function Write($Header, $String = ''){
            $Command = Pack('VV', ++$this->RconRequestId, $Header).$String."\x00\x00"; 

            $Command = Pack('V', StrLen($Command)).$Command;
            $Length  = StrLen($Command);
            
            return $Length === FWrite($this->RconSocket, $Command, $Length);
        }
        
        private function Read(){
            $Buffer = new Buffer();
            $Buffer->Set(FRead($this->RconSocket, 4));
            
            if($Buffer->Remaining() < 4) return false;
            
            $PacketSize = $Buffer->GetLong();
            
            $Buffer->Set(FRead($this->RconSocket, $PacketSize));

            $Data = $Buffer->Get();
            $Remaining = $PacketSize - StrLen($Data);
            
            while($Remaining > 0){
                $Data2 = FRead($this->RconSocket, $Remaining);
                $PacketSize = StrLen($Data2);
                
                if($PacketSize === 0)return false;
            
                $Data .= $Data2;
                $Remaining -= $PacketSize;
            }

            $Buffer->Set($Data);
            return $Buffer;
        }
        
        public function Command($Command){
            $this->Write(2, $Command);

            $Buffer = $this->Read();
            $Buffer->GetLong();
            $Type = $Buffer->GetLong();
            
            if($Type === 2)return false;
            else if($Type !== 0)return false;
            
            $Data = $Buffer->Get();
            
            if(StrLen( $Data ) >= 4000){
                do{
                    $this->Write(0);
                    
                    $Buffer = $this->Read();
                    if($Buffer->GetLong() !== 0)break;
                    
                    $Data2 = $Buffer->Get();
                    if($Data2 === "\x00\x01\x00\x00\x00\x00")break;
                    
                    $Data .= $Data2;
                }
                while(true);
            }
            return rtrim($Data, "\0");
        }
        
        public function RconPass($Password){
            $this->Write(3, $Password);
            $Buffer = $this->Read();
            
            $RequestID = $Buffer->GetLong();
            $Type      = $Buffer->GetLong();
            if($Type === 0){
                $Buffer = $this->Read();
                $RequestID = $Buffer->GetLong();
                $Type      = $Buffer->GetLong();
            }
            
            if($RequestID === -1 || $Type !== 2)return false;
        }
    }