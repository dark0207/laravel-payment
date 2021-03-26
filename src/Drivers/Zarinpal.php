<?php

namespace Omalizadeh\MultiPayment\Drivers;

use Omalizadeh\MultiPayment\Exceptions\InvalidConfigurationException;
use Omalizadeh\MultiPayment\Exceptions\PaymentFailedException;
use Omalizadeh\MultiPayment\Exceptions\PurchaseFailedException;
use Omalizadeh\MultiPayment\RedirectionForm;
use SoapClient;

class Zarinpal extends Driver
{
    public function purchase(): string
    {
        $data = $this->getPurchaseData();
        $client = new SoapClient($this->getPurchaseUrl(), ['encoding' => 'UTF-8']);
        $result = $client->PaymentRequest($data);
        if ($result->Status != $this->getSuccessResponseStatusCode() or empty($result->Authority)) {
            $message = $this->getStatusMessage($result->Status);
            throw new PurchaseFailedException($message, $result->Status);
        }
        $this->invoice->setTransactionId($result->Authority);

        return $result->Authority;
    }

    public function pay(): RedirectionForm
    {
        $transactionId = $this->invoice->getTransactionId();
        $paymentUrl = $this->getPaymentUrl();
        if (strtolower($this->getMode()) == 'zaringate') {
            $payUrl = str_replace(':authority', $transactionId, $paymentUrl);
        } else {
            $payUrl = $paymentUrl . $transactionId;
        }

        return $this->redirectWithForm($payUrl, [], 'GET');
    }

    public function verify(): string
    {
        $status = request('Status');
        if ($status != 'OK') {
            throw new PaymentFailedException('عملیات پرداخت ناموفق بود یا توسط کاربر لغو شد.');
        }
        $data = $this->getVerificationData();
        $client = new SoapClient($this->getVerificationUrl(), ['encoding' => 'UTF-8']);
        $result = $client->PaymentVerification($data);
        if ($result->Status != $this->getSuccessResponseStatusCode()) {
            $message = $this->getStatusMessage($result->Status);
            throw new PaymentFailedException($message, $result->Status);
        }

        return $result->RefID;
    }

    protected function getSuccessResponseStatusCode(): string
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
                $url = 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl';
                break;
            default:
                $url = 'https://ir.zarinpal.com/pg/services/WebGate/wsdl';
                break;
        }

        return $url;
    }

    protected function getPaymentUrl(): string
    {
        $mode = $this->getMode();
        switch ($mode) {
            case 'zaringate':
                $url = 'https://zarinpal.com/pg/StartPay/:authority/ZarinGate';
                break;
            case 'sandbox':
                $url = 'https://sandbox.zarinpal.com/pg/StartPay/';
                break;
            default:
                $url = 'https://zarinpal.com/pg/StartPay/';
                break;
        }

        return $url;
    }

    protected function getVerificationUrl(): string
    {
        $mode = $this->getMode();
        switch ($mode) {
            case 'sandbox':
                $url = 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl';
                break;
            default:
                $url = 'https://ir.zarinpal.com/pg/services/WebGate/wsdl';
                break;
        }

        return $url;
    }

    protected function getPurchaseData(): array
    {
        if (empty($this->settings['merchant_id'])) {
            throw new InvalidConfigurationException('Merchant id has not been set.');
        }
        if (!empty($this->invoice->getDescription())) {
            $description = $this->invoice->getDescription();
        } else {
            $description = $this->settings['description'];
        }
        $mobile = $this->invoice->getPhoneNumber();
        $email = $this->invoice->getEmail();
        return [
            'MerchantID' => $this->settings['merchant_id'],
            'Amount' => $this->invoice->getAmount(),
            'CallbackURL' => $this->settings['callback_url'],
            'Description' => $description,
            'Mobile' => $mobile,
            'Email' => $email,
            'AdditionalData' => $this->invoice->getCustomerInfo()
        ];
    }

    protected function getVerificationData(): array
    {
        $authority = $this->invoice->getTransactionId() ?? request('Authority');
        return [
            'MerchantID' => $this->settings['merchant_id'],
            'Authority' => $authority,
            'Amount' => $this->invoice->getAmount(),
        ];
    }

    private function getMode(): string
    {
        return strtolower(trim($this->settings['mode']));
    }
}
