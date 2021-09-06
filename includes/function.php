<?php


function salesloo_tripay_request_payment($invoice)
{
    $customer = get_userdata($invoice->user_id);
    list($tripay, $channel) = explode('-', $invoice->payment_method);

    $items = [];
    $summary = $invoice->summary;
    foreach ($summary['products'] as $product) {
        $items[] = [
            'name' => $product['label'],
            'price' => $product['value'],
            'quantity' => 1
        ];
    }

    $args = [
        'method'            => $channel,
        'merchant_ref'      => $invoice->number,
        'amount'            => salesloo_convert_price($invoice->total),
        'customer_name'     => salesloo_user_get_name($customer),
        'customer_email'    => $customer->user_email,
        'customer_phone'    => get_user_meta($customer->ID, 'phone', true),
        'order_items'       => $items,
    ];

    $calback_url = get_rest_url(null, 'salesloo-tripay/v1/webhook');
    $return_url = salesloo_url_payment(salesloo_encrypt($invoice->ID));

    $response = Salesloo_Tripay\Tripay::init()->request_payment($args, $calback_url, $return_url);

    return $response;
}

function salesloo_tripay_save_payment($invoice_id, $reference)
{
    $args = [
        'invoice_id' => intval($invoice_id),
        'reference' => $reference
    ];
    $payment_id = Salesloo_Tripay\Models\Payment::data($args)->create();

    return $payment_id;
}


function salesloo_tripay_get_payment($invoice)
{
    $payment = Salesloo_Tripay\Models\Payment::query('WHERE invoice_id = %d', $invoice->ID)->order('ID', 'DESC')->first();

    if ($payment->ID > 0) {

        $reference = $payment->reference;

        $data = Salesloo_Tripay\Tripay::init()->get_payment($reference);

        if ($data && $data['expired_time'] > strtotime('now')) {
            return $data;
        }
    }

    $data = salesloo_tripay_request_payment($invoice);
    salesloo_tripay_save_payment($invoice->ID, $data['reference']);

    return $data;
}


function salesloo_tripay_payment_print_action($class)
{
    if (in_array(___salesloo('invoice')->status, ['cancelled', 'completed'])) return;

    $data = ___salesloo('tripay_payment_data');
    $instructions = $data['instructions'];
    $title = in_array($class->get_id(), ['tripay-ALFAMART', 'tripay-ALFAMIDI']) ? 'Kode Pembayaran' : 'Nomor Virtual account';

    $pay_code = $data['pay_code'];

    if ($class->get_id() == 'tripay-QRIS') {
        $title = '';
        $pay_code = '<img class="w-52" src="' . $data['qr_url'] . '"/>';
    }
    ob_start();

?>
    <div class="border-t border-dashed py-10">
        <div class="flex flex-col sm:flex-row space-y-5 sm:space-y-0 items-center">
            <div class="w-full sm:w-1/2">
                <div class="mb-2">
                    <img class="w-28 h-auto" src="<?php echo $class->get_icon(); ?>">
                </div>
                <div class="text-base font-semibold text-gray-500"><?php echo $class->get_title(); ?></div>
                <div class="text-sm text-gray-400"><?php echo $class->get_description(); ?></div>
            </div>
            <div class="flex-grow">
                <div class="flex items-center h-full justify-center sm:justify-end">
                    <div class="w-auto text-center">
                        <div class="text-sm text-gray-500">
                            <?php echo $title; ?>
                        </div>
                        <div class="text-xl font-bold text-gray-600 my-1">
                            <?php echo $pay_code; ?>
                        </div>
                        <?php if ($class->get_id() != 'tripay-QRIS') : ?>
                            <div class="text-center">
                                <div class="clipboard text-blue-700 w-16 mx-auto" @click="$copy(<?php echo $data['pay_code']; ?>)">
                                    <div class="flex items-center leading-lg px-1 whitespace-no-wrap text-grey-900 text-xs cursor-pointer font-bold">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                        <span class="ml-1">Copy</span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-dashed py-10">
        <div class="text-lg font-semibold text-teal-500 text-center"><?php echo 'Cara pembayaran'; ?></div>
        <?php foreach ((array)$instructions as $instruction) : ?>
            <div class="ml-5 p-10 flex flex-col justify-center max-w-2xl rounded bg-teal-200">
                <div class="text-base font-semibold text-gray-600"><?php echo $instruction['title']; ?></div>
                <div class="mt-4 text-gray-500 text-sm">
                    <?php foreach ((array)$instruction['steps'] as $key => $text) : ?>
                        <div class="flex">
                            <div class="w-8 text-center py-1">
                                <p class="text-3xl p-0 text-green-dark">&bull;</p>
                            </div>
                            <div class="w-4/5 py-3 px-1">
                                <p class="hover:text-blue-dark"><?php echo $text; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

<?php

    return ob_get_clean();
}

function salesloo_tripay_payment_handle_action($invoice)
{
    global $___salesloo;

    if (in_array($invoice->status, ['cancelled', 'completed'])) return;

    $payment = salesloo_tripay_get_payment($invoice);

    $___salesloo['tripay_payment_data'] = $payment;
}
