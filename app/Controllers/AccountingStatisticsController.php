<?php

namespace App\Controllers;

use App\Models\AccountingStatisticsModel;

class AccountingStatisticsController extends BaseController
{
    protected $accountingStatisticsModel;

    public function __construct()
    {
        $this->accountingStatisticsModel = new AccountingStatisticsModel();
    }

    public function index()
    {
        $filters = [
            'customer_code' => $this->request->getGet('customer_code') ?? '',
            'from_date' => $this->request->getGet('from_date'),
            'to_date' => $this->request->getGet('to_date'),
        ];
        $data['debtSummary'] = $this->accountingStatisticsModel->getCustomerDebtSummary($filters);
        $data['filters'] = $filters;
        return view('statistics/accounting', $data);
    }
}
