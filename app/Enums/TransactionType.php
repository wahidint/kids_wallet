<?php

namespace App\Enums;

/**
 * Direction of money movement.
 */
enum TransactionType: string
{
    case Credit = 'credit'; // money IN to the wallet
    case Debit = 'debit';   // money OUT of the wallet
}