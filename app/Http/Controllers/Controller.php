<?php

namespace App\Http\Controllers;

abstract class Controller extends BaseController
{
    // Legacy alias kept to avoid breaking controllers that have not yet been
    // migrated to BaseController or AuthenticatedController.
}
