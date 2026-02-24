<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    public static function log(
        string $logName,
        string $event,
        string $action,
        string $description,
        array $properties = [],
        array $metadata = [],
        ?Model $causer = null,
        ?Model $subject = null,
    ): void {
        try {
            $sanitizedProperties = AuditPayloadSanitizer::sanitizeWithMaskedFields($properties);
            $sanitizedMetadata = AuditPayloadSanitizer::sanitize($metadata);

            $mandatoryMetadata = [
                'actor_type' => self::resolveActorType($causer),
                'module' => $sanitizedMetadata['module'] ?? $logName,
                'risk_level' => $sanitizedMetadata['risk_level'] ?? 'medium',
                'result' => $sanitizedMetadata['result'] ?? 'success',
                'request_id' => $sanitizedMetadata['request_id'] ?? request()?->header('X-Request-Id'),
                'correlation_id' => $sanitizedMetadata['correlation_id'] ?? request()?->header('X-Correlation-Id'),
                'masked_fields' => $sanitizedProperties['masked_fields'],
            ];

            $metadataPayload = array_merge($sanitizedMetadata, $mandatoryMetadata);

            $logger = activity($logName)
                ->event($event)
                ->withProperties($sanitizedProperties['payload'])
                ->tap(function ($activity) use ($action, $metadataPayload) {
                    $activity->action = $action;
                    $activity->metadata = $metadataPayload;

                    if (request()) {
                        $activity->ip_address = request()->ip();
                        $activity->user_agent = request()->userAgent();
                    }
                });

            if ($causer) {
                $logger->causedBy($causer);
            }

            if ($subject) {
                $logger->performedOn($subject);
            }

            $logger->log($description);
        } catch (\Throwable $exception) {
            Log::error('Audit logging failed', [
                'log_name' => $logName,
                'event' => $event,
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private static function resolveActorType(?Model $causer): string
    {
        if ($causer === null) {
            return 'system';
        }

        if (method_exists($causer, 'hasRole')) {
            if ($causer->hasRole('super-admin')) {
                return 'super-admin';
            }

            if ($causer->getRoleNames()->contains(fn ($role) => str_contains($role, 'admin'))) {
                return 'admin';
            }
        }

        return 'user';
    }
}
