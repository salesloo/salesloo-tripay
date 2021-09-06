<?php

namespace Salesloo_Tripay;

use \Salesloo\Models\Invoice;
use \Salesloo_Tripay\Models\Payment;

class Webhook
{

    /**
     * register rest api
     */
    public function register_rest_api()
    {
        register_rest_route('salesloo-tripay/v1', '/webhook', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'endpoint'],
            'permission_callback' => [$this, 'check_autorize']
        ));
    }

    /**
     * endpoint response
     */
    public function endpoint(\WP_REST_Request $request)
    {

        $push = (object) json_decode(file_get_contents("php://input"));

        $payment = Payment::query('WHERE reference = %s', $push->reference)->first();
        $invoice_query = "WHERE ID = %d AND payment_method LIKE 'tripay-%' AND status IN ('unpaid', 'checking_payment')";
        $invoice = Invoice::query($invoice_query, $payment->invoice_id)->first();

        if ($payment->ID > 0 && 'PAID' == $push->status && $invoice->total == $push->total_amount) {
            salesloo_update_invoice_status($invoice->ID, 'completed');
        }

        $invoice = salesloo_get_invoice($invoice->ID);

        return new \WP_REST_Response(['success' => true, 'invoice' => $invoice->ID], 200);
    }

    public function check_autorize()
    {
        $callbackSignature = isset($_SERVER['HTTP_X_CALLBACK_SIGNATURE']) ? $_SERVER['HTTP_X_CALLBACK_SIGNATURE'] : '';
        $payload = file_get_contents("php://input");
        $signature = hash_hmac('sha256', $payload, get_option('tripay_private_key'));

        $event = $_SERVER['HTTP_X_CALLBACK_EVENT'];

        if ($callbackSignature == $signature && 'payment_status' == $event) return true;

        return false;
    }
}
