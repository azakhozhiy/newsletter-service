<?php

namespace WaHelp\Newsletter\Enum;

enum NewsletterStatusEnum: string
{
    case NEW = 'NEW';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case PARTIAL_COMPLETED = 'PARTIAL_COMPLETED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case UNKNOWN = 'UNKNOWN';
}
