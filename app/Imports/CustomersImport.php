<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;

class CustomersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Customer([
            'CustomerID' => $row['customerid'],
            'AccountID' => $row['accountid'],
            'CustomerTaxID' => $row['customertaxid'],
            'CompanyName' => $row['companyname'],
            'Telephone' => $row['telephone'],
            'Email' => $row['email'],
            'Website' => $row['website'],
            'SelfBillingIndicator' => $row['selfbillingindicator'],
            'UserID' => $row['userid'],
        ]);
    }

    public function chunkSize(): int
    {
        return 1000; // Define o tamanho do chunk
    }

    public function batchSize(): int
    {
        return 1000; // Define o tamanho do batch
    }
}
