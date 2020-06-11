<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Model;


use Common\Model\Model;

class TransportTaskLogModel extends Model {


    const PROCESS_STATUS_WAITTING = 0;
    const PROCESS_STATUS_PRICESSING = 1;
    const PROCESS_STATUS_FINISH = 2;

}