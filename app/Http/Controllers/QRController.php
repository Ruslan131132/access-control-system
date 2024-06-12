<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\QrUser;
use App\Services\AccessControlService;
use App\Services\QrImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QRController extends Controller
{
    public function __construct(
        private readonly AccessControlService $accessControl,
        private readonly QrImageService $qrImageService,
    ) {}

    public function generate(Request $request)
    {
        $user = $request->user();

        if (! $user->active) {
            return self::error('Учётная запись заблокирована', 403);
        }

        $uniqueId = (string) Str::uuid();
        $image = $this->qrImageService->png(config('app.url').'/api/qr/check/'.$uniqueId);

        $outputFile = '/img/qr-code/img-'.time().'-'.Str::random(6).'.png';
        Storage::disk('public')->put($outputFile, $image);

        $qr = QrUser::create([
            'user_id' => $user->id,
            'unique_id' => $uniqueId,
            'path' => '/storage'.$outputFile,
            'active' => true,
        ]);

        $this->accessControl->record(
            'qr.generated',
            'Сгенерирован новый QR-код',
            Log::SUCCESS,
            $request,
            $user,
            'qr'
        );

        return self::response([
            'path' => $qr->path,
            'unique_id' => $qr->unique_id,
        ], 'QR-код успешно создан');
    }

    public function check(Request $request, string $id)
    {
        $user = auth('sanctum')->user();

        if (! $user) {
            $this->accessControl->deny(
                'qr.unauthorized',
                'Попытка отсканировать QR-код неавторизованным пользователем',
                Log::WARNING,
                $request
            );

            if ($request->ajax() || $request->expectsJson()) {
                return self::error('Нет данных по QR-коду', 400);
            }

            return view('qr.error', ['error' => 'ОШИБКА ПРИ СКАНИРОВАНИИ']);
        }

        if (! $user->active) {
            $this->accessControl->deny(
                'qr.banned',
                'Попытка доступа заблокированным пользователем: '.$user->email,
                Log::WARNING,
                $request,
                $user
            );

            return self::error('Учётная запись заблокирована', 403);
        }

        $qr = QrUser::where('unique_id', $id)->first();

        if (! $qr) {
            $this->accessControl->deny(
                'qr.not_found',
                'Попытка отсканировать несуществующий QR-код',
                Log::WARNING,
                $request,
                $user
            );

            return self::error('Нет данных по QR-коду', 400);
        }

        if ($qr->user_id !== $user->id) {
            $this->accessControl->deny(
                'qr.denied',
                'Попытка отсканировать чужой QR-код под пользователем '.$user->email,
                Log::WARNING,
                $request,
                $user
            );

            return self::error('В доступе отказано', 403);
        }

        if (! $qr->active) {
            $this->accessControl->deny(
                'qr.already_used',
                'Повторное сканирование уже использованного QR-кода',
                Log::FAIL,
                $request,
                $user
            );

            return self::error('QR-код уже был отсканирован', 400);
        }

        $this->accessControl->grant($user, $qr, $request);

        return self::response([
            'success' => true,
        ], 'Дверь успешно открыта');
    }

    public function showActualSession(Request $request)
    {
        $qr = QrUser::query()
            ->where('active', true)
            ->orderByDesc('created_at')
            ->first();

        if ($request->ajax() || $request->expectsJson()) {
            return self::response(
                $qr,
                $qr ? 'Успешно получен QR-код' : 'Зайдите в приложение и QR-код появится'
            );
        }

        return view('qr', compact('qr'));
    }
}
