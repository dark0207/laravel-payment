<?php

namespace Omalizadeh\MultiPayment\Drivers;

use Omalizadeh\MultiPayment\Invoice;
use Omalizadeh\MultiPayment\Receipt;
use Omalizadeh\MultiPayment\RedirectionForm;

class Zarinpal extends Driver
{
    protected array $settings;
    protected Invoice $invoice;

    public function purchase()
    {
        // if (!empty($this->invoice->getDetails()['description'])) {
        //     $description = $this->invoice->getDetails()['description'];
        // } else {
        //     $description = $this->settings->description;
        // }

        // if (!empty($this->invoice->getDetails()['mobile'])) {
        //     $mobile = $this->invoice->getDetails()['mobile'];
        // }

        // if (!empty($this->invoice->getDetails()['email'])) {
        //     $email = $this->invoice->getDetails()['email'];
        // }

        // $data = array(
        //     'MerchantID' => $this->settings->merchantId,
        //     'Amount' => $this->invoice->getAmount(),
        //     'CallbackURL' => $this->settings->callbackUrl,
        //     'Description' => $description,
        //     'Mobile' => $mobile ?? '',
        //     'Email' => $email ?? '',
        //     'AdditionalData' => $this->invoice->getDetails()
        // );

        // $client = new \SoapClient($this->getPurchaseUrl(), ['encoding' => 'UTF-8']);
        // $result = $client->PaymentRequest($data);

        // if ($result->Status != 100 || empty($result->Authority)) {
        //     // some error has happened
        //     $message = $this->translateStatus($result->Status);
        //     throw new PurchaseFailedException($message, $result->Status);
        // }

        // $this->invoice->transactionId($result->Authority);

        // // return the transaction's id
        // return $this->invoice->getTransactionId();
    }

    public function pay(): RedirectionForm
    {
        // $transactionId = $this->invoice->getTransactionId();
        // $paymentUrl = $this->getPaymentUrl();

        // if (strtolower($this->getMode()) == 'zaringate') {
        //     $payUrl = str_replace(':authority', $transactionId, $paymentUrl);
        // } else {
        //     $payUrl = $paymentUrl . $transactionId;
        // }

        // return $this->redirectWithForm($payUrl, [], 'GET');
    }

    public function verify(): Receipt
    {
        // $authority = $this->invoice->getTransactionId() ?? Request::input('Authority');
        // $status = Request::input('Status');

        // $data = [
        //     'MerchantID' => $this->settings->merchantId,
        //     'Authority' => $authority,
        //     'Amount' => $this->invoice->getAmount(),
        // ];

        // if ($status != 'OK') {
        //     throw new InvalidPaymentException('عملیات پرداخت توسط کاربر لغو شد.', -22);
        // }

        // $client = new \SoapClient($this->getVerificationUrl(), ['encoding' => 'UTF-8']);
        // $result = $client->PaymentVerification($data);

        // if ($result->Status != 100) {
        //     $message = $this->translateStatus($result->Status);
        //     throw new InvalidPaymentException($message, $result->Status);
        // }

        // return $this->createReceipt($result->RefID);
    }

    public function createReceipt($referenceId)
    {
        // return new Receipt('zarinpal', $referenceId);
    }

    protected function getSuccessStatusCode(): string
    {
        return "100";
    }

    protected function getStatusMessage($status): string
    {
        $messages = array(
            "-1" => "اطلاعات ارسال شده ناقص است.",
            "-2" => "IP و يا مرچنت كد پذيرنده صحيح نيست",
            "-3" => "با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد",
            "-4" => "سطح تاييد پذيرنده پايين تر از سطح نقره اي است.",
            "-11" => "درخواست مورد نظر يافت نشد.",
            "-12" => "امكان ويرايش درخواست ميسر نمي باشد.",
            "-21" => "هيچ نوع عمليات مالي براي اين تراكنش يافت نشد",
            "-22" => "تراكنش نا موفق ميباشد",
            "-33" => "رقم تراكنش با رقم پرداخت شده مطابقت ندارد",
            "-34" => "سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است",
            "-40" => "اجازه دسترسي به متد مربوطه وجود ندارد.",
            "-41" => "اطلاعات ارسال شده مربوط به AdditionalData غيرمعتبر ميباشد.",
            "-42" => "مدت زمان معتبر طول عمر شناسه پرداخت بايد بين 30 دقيه تا 45 روز مي باشد.",
            "-54" => "درخواست مورد نظر آرشيو شده است",
            "101" => "عمليات پرداخت موفق بوده و قبلا PaymentVerification تراكنش انجام شده است.",
        );
        $unknownError = 'خطای ناشناخته رخ داده است.';

        return array_key_exists($status, $messages) ? $messages[$status] : $unknownError;
    }

    protected function getPurchaseUrl(): string
    {
        $mode = $this->getMode();
        switch ($mode) {
            case 'sandbox':
                $url = $this->settings['sandboxPurchaseApiUrl'];
                break;
            case 'zaringate':
                $url = $this->settings['zaringatePurchaseApiUrl'];
                break;
            default:
                $url = $this->settings['purchaseApiUrl'];
                break;
        }

        return $url;
    }

    protected function getPaymentUrl(): string
    {
        $mode = $this->getMode();
        switch ($mode) {
            case 'zaringate':
                $url = $this->settings['zaringatePaymentApiUrl'];
                break;
            case 'sandbox':
                $url = $this->settings['sandboxPaymentApiUrl'];
                break;
            default:
                $url = $this->settings['paymentApiUrl'];
                break;
        }

        return $url;
    }

    protected function getVerificationUrl(): string
    {
        $mode = $this->getMode();
        switch ($mode) {
            case 'zaringate':
                $url = $this->settings['zaringateVerificationApiUrl'];
                break;
            case 'sandbox':
                $url = $this->settings['sandboxVerificationApiUrl'];
                break;
            default:
                $url = $this->settings['apiVerificationUrl'];
                break;
        }

        return $url;
    }

    private function getMode(): string
    {
        return strtolower($this->settings['mode']);
    }
}
