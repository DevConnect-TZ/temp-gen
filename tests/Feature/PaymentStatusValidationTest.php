<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaymentStatusValidationTest extends TestCase
{
    public function test_check_status_returns_json_validation_error_instead_of_redirect(): void
    {
        $response = $this->post(route('payments.check-status'), []);

        $response->assertUnprocessable();
        $response->assertHeader('Content-Type', 'application/json');
        $response->assertJsonPath('status', 'error');
        $response->assertJsonValidationErrorFor('transaction_id', 'errors');
    }
}
