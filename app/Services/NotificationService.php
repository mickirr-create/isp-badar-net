<?php

namespace App\Services;

use App\Models\AppConfig;
use App\Models\Customer;
use App\Models\CustomerInbox;
use App\Models\UserRecharge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send due soon notification to customer
     */
    public function sendDueSoonNotification(UserRecharge $recharge): void
    {
        $customer = $recharge->customer;
        if (!$customer) {
            return;
        }

        $dueDate = app(BillingCycleService::class)->getDueDate($customer);
        if (!$dueDate) {
            return;
        }

        $channels = explode(',', AppConfig::getSetting('billing_notify_channels', 'inbox'));

        foreach ($channels as $channel) {
            $channel = trim($channel);

            switch ($channel) {
                case 'email':
                    $this->sendEmailNotification($customer, $recharge, $dueDate);
                    break;
                case 'whatsapp':
                    $this->sendWhatsAppNotification($customer, $recharge, $dueDate);
                    break;
                case 'inbox':
                    $this->sendInboxNotification($customer, $recharge, $dueDate);
                    break;
            }
        }

        Log::info("Due soon notification sent to {$customer->username} via " . implode(', ', $channels));
    }

    /**
     * Send throttle notification to customer
     */
    public function sendThrottleNotification(UserRecharge $recharge): void
    {
        $customer = $recharge->customer;
        if (!$customer) {
            return;
        }

        $channels = explode(',', AppConfig::getSetting('billing_notify_channels', 'inbox'));

        $message = "Yang terhormat {$customer->name},\n\n";
        $message .= "Kami informasikan bahwa akses internet Anda telah dibatasi (throttle) ";
        $message .= "karena melewati tanggal jatuh tempo billing.\n\n";
        $message .= "Untuk mengembalikan kecepatan normal, silakan lakukan pembayaran.\n\n";
        $message .= "Terima kasih,\n" . AppConfig::getSetting('company_name', 'Badar Net');

        foreach ($channels as $channel) {
            $channel = trim($channel);

            switch ($channel) {
                case 'email':
                    $this->sendEmail($customer, 'Pembatasan Akses Internet', $message);
                    break;
                case 'whatsapp':
                    $this->sendWhatsApp($customer, $message);
                    break;
                case 'inbox':
                    $this->sendInbox($customer, 'Pembatasan Akses Internet', $message);
                    break;
            }
        }
    }

    /**
     * Send restore notification to customer
     */
    public function sendRestoreNotification(UserRecharge $recharge): void
    {
        $customer = $recharge->customer;
        if (!$customer) {
            return;
        }

        $channels = explode(',', AppConfig::getSetting('billing_notify_channels', 'inbox'));

        $message = "Yang terhormat {$customer->name},\n\n";
        $message .= "Pembayaran Anda telah berhasil diproses.\n";
        $message .= "Kecepatan internet Anda telah dikembalikan ke paket {$recharge->namebp}.\n\n";
        $message .= "Terima kasih,\n" . AppConfig::getSetting('company_name', 'Badar Net');

        foreach ($channels as $channel) {
            $channel = trim($channel);

            switch ($channel) {
                case 'email':
                    $this->sendEmail($customer, 'Pembayaran Berhasil', $message);
                    break;
                case 'whatsapp':
                    $this->sendWhatsApp($customer, $message);
                    break;
                case 'inbox':
                    $this->sendInbox($customer, 'Pembayaran Berhasil', $message);
                    break;
            }
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification(Customer $customer, UserRecharge $recharge, Carbon $dueDate): void
    {
        $companyName = AppConfig::getSetting('company_name', 'Badar Net');
        $subject = "Pengingat Jatuh Tempo - {$companyName}";

        $message = "Yang terhormat {$customer->name},\n\n";
        $message .= "Ini adalah pengingat bahwa pembayaran untuk paket internet Anda akan jatuh tempo pada:\n\n";
        $message .= "Tanggal Jatuh Tempo: {$dueDate->format('d/m/Y')}\n";
        $message .= "Paket: {$recharge->namebp}\n\n";
        $message .= "Silakan lakukan pembayaran sebelum tanggal jatuh tempo untuk menghindari pembatasan akses.\n\n";
        $message .= "Terima kasih,\n{$companyName}";

        $this->sendEmail($customer, $subject, $message);
    }

    /**
     * Send WhatsApp notification
     */
    private function sendWhatsAppNotification(Customer $customer, UserRecharge $recharge, Carbon $dueDate): void
    {
        $companyName = AppConfig::getSetting('company_name', 'Badar Net');

        $message = "Halo {$customer->name},\n\n";
        $message .= "Pengingat jatuh tempo billing internet:\n";
        $message .= "- Tanggal: {$dueDate->format('d/m/Y')}\n";
        $message .= "- Paket: {$recharge->namebp}\n\n";
        $message .= "Silakan bayar sebelum jatuh tempo.\n";
        $message .= "- {$companyName}";

        $this->sendWhatsApp($customer, $message);
    }

    /**
     * Send inbox notification
     */
    private function sendInboxNotification(Customer $customer, UserRecharge $recharge, Carbon $dueDate): void
    {
        $companyName = AppConfig::getSetting('company_name', 'Badar Net');
        $subject = "Pengingat Jatuh Tempo";

        $message = "Pengingat jatuh tempo billing internet:\n";
        $message .= "- Tanggal: {$dueDate->format('d/m/Y')}\n";
        $message .= "- Paket: {$recharge->namebp}\n\n";
        $message .= "Silakan bayar sebelum jatuh tempo.";

        $this->sendInbox($customer, $subject, $message);
    }

    /**
     * Send email using Laravel Mail
     */
    private function sendEmail(Customer $customer, string $subject, string $message): void
    {
        if (!$customer->email) {
            return;
        }

        // TODO: Implement with Laravel Mail or notification
        Log::info("Email notification to {$customer->email}: {$subject}");
    }

    /**
     * Send WhatsApp using configured gateway
     */
    private function sendWhatsApp(Customer $customer, string $message): void
    {
        if (!$customer->phone) {
            return;
        }

        // TODO: Implement with WhatsApp gateway
        Log::info("WhatsApp notification to {$customer->phone}: sent");
    }

    /**
     * Send inbox notification
     */
    private function sendInbox(Customer $customer, string $subject, string $message): void
    {
        CustomerInbox::create([
            'customer_id' => $customer->id,
            'subject' => $subject,
            'message' => $message,
            'status' => 'unread',
            'created_at' => now(),
        ]);

        // Log to message_logs
        DB::table('tbl_message_logs')->insert([
            'message_type' => 'inbox',
            'recipient' => $customer->username,
            'message_content' => $message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
