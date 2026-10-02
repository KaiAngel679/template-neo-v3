<?php

declare(strict_types=1);

namespace MaxMind\Db;

use MaxMind\Db\Reader\Decoder;
use MaxMind\Db\Reader\InvalidDatabaseException;
use MaxMind\Db\Reader\Metadata;
use MaxMind\Db\Reader\Util;

class Reader
{
    private static $DATA_SECTION_SEPARATOR_SIZE = 16;
    private static $METADATA_START_MARKER = "\xAB\xCD\xEFMaxMind.com";
    private static $METADATA_START_MARKER_LENGTH = 14;
    private static $METADATA_MAX_SIZE = 131072;
    private $decoder;
    private $fileHandle;
    private $fileSize;
    private $ipV4Start;
    private $metadata;

    public function __construct(string $database)
    {
        if (\func_num_args() !== 1) {
            throw new \ArgumentCountError(
                \sprintf('%s() expects exactly 1 parameter, %d given', __METHOD__, \func_num_args())
            );
        }

        if (is_dir($database)) {
            throw new InvalidDatabaseException(
                "Error opening database file ($database). Is this a valid MaxMind DB file?"
            );
        }

        $fileHandle = @fopen($database, 'rb');
        if ($fileHandle === false) {
            throw new \InvalidArgumentException(
                "The file \"$database\" does not exist or is not readable."
            );
        }
        $this->fileHandle = $fileHandle;

        $fstat = fstat($fileHandle);
        if ($fstat === false) {
            throw new \UnexpectedValueException(
                "Error determining the size of \"$database\"."
            );
        }
        $this->fileSize = $fstat['size'];

        $start = $this->findMetadataStart($database);
        $metadataDecoder = new Decoder($this->fileHandle, $start);
        [$metadataArray] = $metadataDecoder->decode($start);
        $this->metadata = new Metadata($metadataArray);
        $this->decoder = new Decoder(
            $this->fileHandle,
            $this->metadata->searchTreeSize + self::$DATA_SECTION_SEPARATOR_SIZE
        );
        $this->ipV4Start = $this->ipV4StartNode();
    }

    public function get(string $ipAddress)
    {
        if (\func_num_args() !== 1) {
            throw new \ArgumentCountError(
                \sprintf('%s() expects exactly 1 parameter, %d given', __METHOD__, \func_num_args())
            );
        }
        [$record] = $this->getWithPrefixLen($ipAddress);

        return $record;
    }

    public function getWithPrefixLen(string $ipAddress): array
    {
        if (\func_num_args() !== 1) {
            throw new \ArgumentCountError(
                \sprintf('%s() expects exactly 1 parameter, %d given', __METHOD__, \func_num_args())
            );
        }

        if (!\is_resource($this->fileHandle)) {
            throw new \BadMethodCallException(
                'Attempt to read from a closed MaxMind DB.'
            );
        }

        [$pointer, $prefixLen] = $this->findAddressInTree($ipAddress);
        if ($pointer === 0) {
            return [null, $prefixLen];
        }

        return [$this->resolveDataPointer($pointer), $prefixLen];
    }

    private function findAddressInTree(string $ipAddress): array
    {
        $packedAddr = @inet_pton($ipAddress);
        if ($packedAddr === false) {
            throw new \InvalidArgumentException(
                "The value \"$ipAddress\" is not a valid IP address."
            );
        }

        $rawAddress = unpack('C*', $packedAddr);
        if ($rawAddress === false) {
            throw new InvalidDatabaseException(
                'Could not unpack the unsigned char of the packed in_addr representation.'
            );
        }

        $bitCount = \count($rawAddress) * 8;
        $node = 0;
        $metadata = $this->metadata;

        if ($metadata->ipVersion === 6) {
            if ($bitCount === 32) {
                $node = $this->ipV4Start;
            }
        } elseif ($metadata->ipVersion === 4 && $bitCount === 128) {
            throw new \InvalidArgumentException(
                "Error looking up $ipAddress. You attempted to look up an"
                    . ' IPv6 address in an IPv4-only database.'
            );
        }

        $nodeCount = $metadata->nodeCount;

        for ($i = 0; $i < $bitCount && $node < $nodeCount; ++$i) {
            $tempBit = 0xFF & $rawAddress[($i >> 3) + 1];
            $bit = 1 & ($tempBit >> 7 - ($i % 8));

            $node = $this->readNode($node, $bit);
        }
        if ($node === $nodeCount) {
            return [0, $i];
        }
        if ($node > $nodeCount) {
            return [$node, $i];
        }

        throw new InvalidDatabaseException(
            'Invalid or corrupt database. Maximum search depth reached without finding a leaf node'
        );
    }

    private function ipV4StartNode(): int
    {
        if ($this->metadata->ipVersion === 4) {
            return 0;
        }

        $node = 0;

        for ($i = 0; $i < 96 && $node < $this->metadata->nodeCount; ++$i) {
            $node = $this->readNode($node, 0);
        }

        return $node;
    }

    private function readNode(int $nodeNumber, int $index): int
    {
        $baseOffset = $nodeNumber * $this->metadata->nodeByteSize;

        switch ($this->metadata->recordSize) {
            case 24:
                $bytes = Util::read($this->fileHandle, $baseOffset + $index * 3, 3);
                $rc = unpack('N', "\x00" . $bytes);
                if ($rc === false) {
                    throw new InvalidDatabaseException(
                        'Could not unpack the unsigned long of the node.'
                    );
                }
                [, $node] = $rc;

                return $node;

            case 28:
                $bytes = Util::read($this->fileHandle, $baseOffset + 3 * $index, 4);
                if ($index === 0) {
                    $middle = (0xF0 & \ord($bytes[3])) >> 4;
                } else {
                    $middle = 0x0F & \ord($bytes[0]);
                }
                $rc = unpack('N', \chr($middle) . substr($bytes, $index, 3));
                if ($rc === false) {
                    throw new InvalidDatabaseException(
                        'Could not unpack the unsigned long of the node.'
                    );
                }
                [, $node] = $rc;

                return $node;

            case 32:
                $bytes = Util::read($this->fileHandle, $baseOffset + $index * 4, 4);
                $rc = unpack('N', $bytes);
                if ($rc === false) {
                    throw new InvalidDatabaseException(
                        'Could not unpack the unsigned long of the node.'
                    );
                }
                [, $node] = $rc;

                return $node;

            default:
                throw new InvalidDatabaseException(
                    'Unknown record size: '
                        . $this->metadata->recordSize
                );
        }
    }

    private function resolveDataPointer(int $pointer)
    {
        $resolved = $pointer - $this->metadata->nodeCount
            + $this->metadata->searchTreeSize;
        if ($resolved >= $this->fileSize) {
            throw new InvalidDatabaseException(
                "The MaxMind DB file's search tree is corrupt"
            );
        }

        [$data] = $this->decoder->decode($resolved);

        return $data;
    }

    private function findMetadataStart(string $filename): int
    {
        $handle = $this->fileHandle;
        $fileSize = $this->fileSize;
        $marker = self::$METADATA_START_MARKER;
        $markerLength = self::$METADATA_START_MARKER_LENGTH;

        $minStart = $fileSize - min(self::$METADATA_MAX_SIZE, $fileSize);

        for ($offset = $fileSize - $markerLength; $offset >= $minStart; --$offset) {
            if (fseek($handle, $offset) !== 0) {
                break;
            }

            $value = fread($handle, $markerLength);
            if ($value === $marker) {
                return $offset + $markerLength;
            }
        }

        throw new InvalidDatabaseException(
            "Error opening database file ($filename). "
                . 'Is this a valid MaxMind DB file?'
        );
    }

    public function metadata(): Metadata
    {
        if (\func_num_args()) {
            throw new \ArgumentCountError(
                \sprintf('%s() expects exactly 0 parameters, %d given', __METHOD__, \func_num_args())
            );
        }

        if (!\is_resource($this->fileHandle)) {
            throw new \BadMethodCallException(
                'Attempt to read from a closed MaxMind DB.'
            );
        }

        return clone $this->metadata;
    }

    public function close(): void
    {
        if (\func_num_args()) {
            throw new \ArgumentCountError(
                \sprintf('%s() expects exactly 0 parameters, %d given', __METHOD__, \func_num_args())
            );
        }

        if (!\is_resource($this->fileHandle)) {
            throw new \BadMethodCallException(
                'Attempt to close a closed MaxMind DB.'
            );
        }
        fclose($this->fileHandle);
    }
}
