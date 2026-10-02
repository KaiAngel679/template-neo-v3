<?php

declare(strict_types=1);

namespace MaxMind\Db\Reader;

class Metadata
{
    public $binaryFormatMajorVersion;
    public $binaryFormatMinorVersion;
    public $buildEpoch;
    public $databaseType;
    public $description;
    public $ipVersion;
    public $languages;
    public $nodeByteSize;
    public $nodeCount;
    public $recordSize;
    public $searchTreeSize;

    public function __construct(array $metadata)
    {
        if (\func_num_args() !== 1) {
            throw new \ArgumentCountError(
                \sprintf('%s() expects exactly 1 parameter, %d given', __METHOD__, \func_num_args())
            );
        }

        $this->binaryFormatMajorVersion
            = $metadata['binary_format_major_version'];
        $this->binaryFormatMinorVersion
            = $metadata['binary_format_minor_version'];
        $this->buildEpoch = $metadata['build_epoch'];
        $this->databaseType = $metadata['database_type'];
        $this->languages = $metadata['languages'];
        $this->description = $metadata['description'];
        $this->ipVersion = $metadata['ip_version'];
        $this->nodeCount = $metadata['node_count'];
        $this->recordSize = $metadata['record_size'];
        $this->nodeByteSize = $this->recordSize / 4;
        $this->searchTreeSize = $this->nodeCount * $this->nodeByteSize;
    }
}
