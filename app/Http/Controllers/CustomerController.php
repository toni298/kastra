<?php

namespace App\Http\Controllers;

/**
 * Modular sales customer endpoint.
 *
 * The customer CRUD implementation remains in SalesCustomerController during
 * this transition; this controller provides the canonical /sales/customers
 * route target required by the modular Sales architecture.
 */
class CustomerController extends SalesCustomerController {}
