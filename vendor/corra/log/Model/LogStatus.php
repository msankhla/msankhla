<?php

namespace Corra\Log\Model;

/**
 * Class LogStatus
 *
 * Corra\Log\Model
 */
class LogStatus
{
    /**
     * @var string
     */
    const SUCCESS = 'success';

    /**
     * @var string
     */
    const FAILED = 'failed';

    /**
     * @var string
     */
    const COMPLETED = 'completed';

    /**
     * @var string
     */
    const INCOMPLETE = 'incomplete';

    /**
     * @var string
     */
    const PROCESSED = 'processed';

    /**
     * @var string
     */
    const PARTIALLYPROCESSED = 'partially-processed';

    /**
     * @var string
     */
    const INPROGRESS = 'in-progress';

    /**
     * @var string
     */
    const NA = 'na';
}
