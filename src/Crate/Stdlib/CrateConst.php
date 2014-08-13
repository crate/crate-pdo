<?php
/**
 * @author Antoine Hedgcock
 */

namespace Crate\Stdlib;

final class CrateConst
{
    /**
     * Ansi error code: 42000
     *
     * @var int
     */
    const ERR_INVALID_SQL = 4000;
    const ERR_INVALID_ANALYZER_DEF = 4001;
    const ERR_INVALID_TABLE_NAME = 4002;
    const ERR_FIELD_TYPE_VALIDATION = 4003;
    const ERR_FEATURE_NOT_AVAILABLE = 4004;
    const ERR_ALTER_TABLE_USING_ALIAS = 4005;
    const ERR_AMBIGUOUS_COLUMN = 4006;
    const ERR_UNKNOWN_TABLE = 4041;
    const ERR_UNKNOWN_ANALYZER = 4042;
    const ERR_UNKNOWN_COLUMN = 4043;
    const ERR_UNKNOWN_TYPE = 4044;
    const ERR_UNKNOWN_SCHEMA = 4045;
    const ERR_UNKNOWN_PARTITION = 4046;
    const ERR_PRIMARY_EXISTS = 4091;
    const ERR_VERSION_CONFLICT = 4092;
    const ERR_TABLE_EXISTS = 4093;
    const ERR_TABLE_SCHEMA_MISS_MATCH = 4095;
    const ERR_SERVER_ERROR = 5000;
    const ERR_TASK_EXECUTION = 5001;
    const ERR_SHARD_UNAVAILABLE = 5002;
}
