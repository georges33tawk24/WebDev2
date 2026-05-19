<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $titleReplace
     * @param  array<string, mixed>  $bodyReplace
     * @param  array<string, mixed>  $bodySuffixReplace
     */
    public function notify(
        User $user,
        string $titleKey,
        array $titleReplace,
        string $bodyKey,
        array $bodyReplace,
        array $data = [],
        ?string $bodySuffixKey = null,
        array $bodySuffixReplace = [],
    ): Notification {
        $i18n = [
            'title_key' => $titleKey,
            'title_replace' => $titleReplace,
            'body_key' => $bodyKey,
            'body_replace' => $bodyReplace,
        ];

        if ($bodySuffixKey !== null) {
            $i18n['body_suffix_key'] = $bodySuffixKey;
            $i18n['body_suffix_replace'] = $bodySuffixReplace;
        }

        $data['i18n'] = $i18n;

        $preview = new Notification(['data' => $data]);
        $title = $preview->localizedTitle();
        $body = $preview->localizedBody();

        $notification = Notification::query()->create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        $pushData = array_merge($data, [
            'url' => $this->notificationUrl($data),
        ]);

        app(PushNotificationService::class)->send($user, $title, $body, $pushData);
        app(LiveUpdateService::class)->bump($user);

        return $notification;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function notificationUrl(array $data): string
    {
        return match ($data['type'] ?? null) {
            'request', 'request_status', 'document', 'payment' => route('citizen.requests'),
            'chat' => isset($data['service_request_id'])
                ? route('citizen.chat', $data['service_request_id'])
                : route('citizen.chats.index'),
            'appointment' => route('citizen.appointments'),
            'feedback', 'feedback_reply' => route('citizen.requests'),
            default => url('/'),
        };
    }

    /**
     * @return Collection<int, User>
     */
    public function officeStaffFor(int $officeId): Collection
    {
        return User::query()
            ->where('office_id', $officeId)
            ->whereHas('role', fn ($query) => $query->where('slug', 'office_staff'))
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function admins(): Collection
    {
        return User::query()
            ->whereHas('role', fn ($query) => $query->where('slug', 'admin'))
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $titleReplace
     * @param  array<string, mixed>  $bodyReplace
     */
    public function notifyOfficeStaff(
        int $officeId,
        string $titleKey,
        array $titleReplace,
        string $bodyKey,
        array $bodyReplace,
        array $data = [],
    ): void {
        foreach ($this->officeStaffFor($officeId) as $staff) {
            $this->notify($staff, $titleKey, $titleReplace, $bodyKey, $bodyReplace, $data);
        }
    }

    /**
     * @param  array<string, mixed>  $titleReplace
     * @param  array<string, mixed>  $bodyReplace
     * @param  array<string, mixed>  $data
     */
    public function notifyAdmins(
        string $titleKey,
        array $titleReplace,
        string $bodyKey,
        array $bodyReplace,
        array $data = [],
    ): void {
        foreach ($this->admins() as $admin) {
            $this->notify($admin, $titleKey, $titleReplace, $bodyKey, $bodyReplace, $data);
        }
    }

    public function newServiceRequest(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing(['service', 'office', 'citizen']);

        $ref = $serviceRequest->reference_number;
        $data = [
            'type' => 'request',
            'service_request_id' => $serviceRequest->id,
        ];

        $this->notifyOfficeStaff(
            (int) $serviceRequest->office_id,
            'ui.notifications.new_request',
            [],
            'ui.notifications.new_request_body',
            [
                'ref' => $ref,
                'service' => ['service_id' => $serviceRequest->service_id],
            ],
            $data,
        );

        $this->notifyAdmins(
            'ui.notifications.admin_new_request',
            [],
            'ui.notifications.admin_new_request_body',
            [
                'ref' => $ref,
                'service' => ['service_id' => $serviceRequest->service_id],
                'office' => ['office_id' => $serviceRequest->office_id],
            ],
            $data,
        );
    }

    public function requestStatusUpdated(
        ServiceRequest $serviceRequest,
        string $oldStatus,
        string $newStatus,
        ?string $comment = null,
    ): void {
        if ($oldStatus === $newStatus) {
            return;
        }

        $serviceRequest->loadMissing(['service', 'citizen']);

        $citizen = $serviceRequest->citizen;
        if (! $citizen) {
            return;
        }

        $this->notify(
            $citizen,
            'ui.notifications.request_status_updated',
            [],
            'ui.notifications.request_status_body',
            [
                'ref' => $serviceRequest->reference_number,
                'status' => ['status' => $newStatus],
                'service' => ['service_id' => $serviceRequest->service_id],
            ],
            [
                'type' => 'request_status',
                'service_request_id' => $serviceRequest->id,
                'status' => $newStatus,
            ],
            $comment ? 'ui.notifications.staff_comment_suffix' : null,
            $comment ? ['comment' => $comment] : [],
        );
    }

    public function paymentCompleted(ServiceRequest $serviceRequest, Payment $payment): void
    {
        $serviceRequest->loadMissing(['service', 'citizen']);

        $ref = $serviceRequest->reference_number;
        $amount = number_format((float) $payment->amount, 2).' '.$payment->currency;
        $data = [
            'type' => 'payment',
            'service_request_id' => $serviceRequest->id,
            'payment_id' => $payment->id,
        ];

        if ($serviceRequest->citizen) {
            $this->notify(
                $serviceRequest->citizen,
                'ui.notifications.payment_confirmed',
                [],
                'ui.notifications.payment_confirmed_body',
                [
                    'ref' => $ref,
                    'amount' => $amount,
                ],
                $data,
            );
        }

        $this->notifyOfficeStaff(
            (int) $serviceRequest->office_id,
            'ui.notifications.payment_received',
            [],
            'ui.notifications.payment_received_body',
            [
                'ref' => $ref,
                'amount' => $amount,
                'citizen' => $serviceRequest->citizen?->name ?? __('ui.na'),
            ],
            $data,
        );
    }

    public function newFeedback(Feedback $feedback): void
    {
        $feedback->loadMissing(['citizen', 'serviceRequest.service']);

        $this->notifyOfficeStaff(
            (int) $feedback->office_id,
            'ui.notifications.new_feedback',
            [],
            'ui.notifications.new_feedback_body',
            [
                'citizen' => $feedback->citizen?->name ?? __('ui.na'),
                'rating' => (string) $feedback->rating,
                'ref' => $feedback->serviceRequest?->reference_number ?? __('ui.na'),
            ],
            [
                'type' => 'feedback',
                'feedback_id' => $feedback->id,
                'service_request_id' => $feedback->service_request_id,
            ],
        );
    }

    public function feedbackReply(Feedback $feedback, string $replyType): void
    {
        $feedback->loadMissing(['citizen', 'serviceRequest']);

        $citizen = $feedback->citizen;
        if (! $citizen) {
            return;
        }

        $isPublic = $replyType === 'public';

        $this->notify(
            $citizen,
            $isPublic
                ? 'ui.notifications.feedback_public_reply'
                : 'ui.notifications.feedback_private_reply',
            [],
            'ui.notifications.feedback_reply_body',
            [
                'ref' => $feedback->serviceRequest?->reference_number ?? __('ui.na'),
            ],
            [
                'type' => 'feedback_reply',
                'feedback_id' => $feedback->id,
                'service_request_id' => $feedback->service_request_id,
            ],
        );
    }

    public function staffDocumentUploaded(ServiceRequest $serviceRequest): void
    {
        $serviceRequest->loadMissing(['citizen']);

        if (! $serviceRequest->citizen) {
            return;
        }

        $this->notify(
            $serviceRequest->citizen,
            'ui.notifications.document_uploaded',
            [],
            'ui.notifications.document_uploaded_body',
            [
                'ref' => $serviceRequest->reference_number,
            ],
            [
                'type' => 'document',
                'service_request_id' => $serviceRequest->id,
            ],
        );
    }

    public function newChatMessage(ServiceRequest $serviceRequest, User $sender, User $recipient): void
    {
        $this->notify(
            $recipient,
            'ui.notifications.new_chat_message',
            [],
            'ui.notifications.new_chat_message_body',
            [
                'name' => $sender->name,
                'ref' => $serviceRequest->reference_number,
            ],
            [
                'type' => 'chat',
                'service_request_id' => $serviceRequest->id,
            ],
        );
    }

    public function appointmentBooked(
        User $citizen,
        ?User $staff,
        int $officeId,
        \DateTimeInterface $startsAt,
        int $appointmentId,
        ?int $notifyOfficeId = null,
    ): void {
        $whenReplace = ['when' => ['datetime' => $startsAt]];
        $officeReplace = ['office' => ['office_id' => $officeId]];

        $this->notify(
            $citizen,
            'ui.flash.appointment_booked',
            [],
            'ui.citizen.appointment_confirmed_body',
            array_merge($officeReplace, $whenReplace),
            ['type' => 'appointment', 'appointment_id' => $appointmentId],
        );

        $staffData = ['type' => 'appointment', 'appointment_id' => $appointmentId];

        if ($staff) {
            $this->notify(
                $staff,
                'ui.notifications.new_appointment',
                [],
                'ui.notifications.new_appointment_body',
                array_merge(['citizen' => $citizen->name], $whenReplace),
                $staffData,
            );
        } elseif ($notifyOfficeId) {
            $this->notifyOfficeStaff(
                $notifyOfficeId,
                'ui.notifications.new_appointment',
                [],
                'ui.notifications.new_appointment_body',
                array_merge(['citizen' => $citizen->name], $whenReplace),
                $staffData,
            );
        }
    }

    public function appointmentStatusUpdated(
        User $citizen,
        string $status,
        \DateTimeInterface $startsAt,
        int $appointmentId,
    ): void {
        $this->notify(
            $citizen,
            'ui.notifications.appointment_updated',
            [],
            'ui.notifications.appointment_status_body',
            [
                'status' => ['status' => $status],
                'when' => ['datetime' => $startsAt],
            ],
            ['type' => 'appointment', 'appointment_id' => $appointmentId],
        );
    }
}
