<?php

namespace models;

use bossanova\Model\Model;

class Audit extends Model
{
    // Table configuration
    public $config = array(
        'tableName' => 'audit',
        'primaryKey' => 'audit_id',
        'sequence' => 'audit_audit_id_seq',
        'recordId' => 0
    );
}
