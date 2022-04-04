<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function getUser(Request $request)
    {
        return $request->user();
    }
}
