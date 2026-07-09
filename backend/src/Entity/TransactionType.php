<?php

namespace App\Entity;

enum TransactionType: string
{
    case Income = 'income';
    case Expense = 'expense';
}
