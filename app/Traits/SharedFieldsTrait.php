<?php

// app/Traits/SharedFieldsTrait.php
namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;

trait SharedFieldsTrait
{
    public function sharedFields(Blueprint $table)
    {
        $table->string('CompanyName')->default('Desconhecido');
        $table->string('Contact', 30);
        $table->string('BillingAddress_StreetName', 30);
        $table->string('BillingAddress_BuildingNumber', 15);
        $table->string('BillingAddress_AddressDetail', 250);
        $table->string('City', 50);
        $table->string('PostalCode', 20);
        $table->string('Province', 50);
        $table->string('Country', 2);
        $table->string('Telephone', 20)->nullable();
        $table->string('Fax', 20);
        $table->string('Email')->nullable();
        $table->string('Website', 60)->nullable();
        $table->boolean('SelfBillingIndicator')->default(false);
    }
}
