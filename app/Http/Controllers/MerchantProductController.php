<?php

namespace App\Http\Controllers;

use App\Services\MerchantProductService;
use Illuminate\Http\Request;

class MerchantProductController extends Controller
{
    private $merchantProductService;

    public function __construct(MerchantProductService $merchantProductService)
    {
        $this->merchantProductService = $merchantProductService;
    }
}
