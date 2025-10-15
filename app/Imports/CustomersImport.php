<?php
// app/Imports/CustomersImport.php
namespace App\Imports;

use App\Models\Customer;
use App\Http\Requests\CustomerRequest;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CustomersImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        return Customer::updateOrCreate(
            ['CustomerTaxID' => $row['customertaxid'] ?? null], // evita duplicados
            [
                'CompanyName' => $row['companyname'] ?? null,
                'Telephone' => $row['telephone'] ?? null,
                'Email' => $row['email'] ?? null,
                'Website' => $row['website'] ?? null,
                'SelfBillingIndicator' => $row['selfbillingindicator'] ?? 0,
            ]
        );
    }

    public function rules(): array
    {return (new CustomerRequest())->rules();}

    public function batchSize(): int
    {return 1000;}

    public function chunkSize(): int
    {return 1000;}
}
