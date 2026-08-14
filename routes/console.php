<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment('Ettra Signature Administration System');
})->purpose('Menampilkan identitas aplikasi');

Artisan::command('ettra:create-owner', function () {
    $this->info('Membuat atau memperbarui akun Owner Ettra Signature.');
    $this->newLine();

    $existingOwner = User::withTrashed()
        ->where('account_type', User::ACCOUNT_INTERNAL)
        ->where('role', User::ROLE_OWNER)
        ->first();

    $name = trim((string) $this->ask('Nama Owner', $existingOwner?->name ?? 'Owner'));
    $email = Str::lower(trim((string) $this->ask('Email Owner', $existingOwner?->email)));
    $phoneInput = trim((string) $this->ask('Nomor telepon (opsional)', $existingOwner?->phone ?? ''));
    $password = (string) $this->secret('Kata sandi');
    $passwordConfirmation = (string) $this->secret('Ulangi kata sandi');

    $phoneDigits = preg_replace('/\D+/', '', $phoneInput) ?? '';

    if (str_starts_with($phoneDigits, '62')) {
        $phoneDigits = '0'.substr($phoneDigits, 2);
    } elseif (str_starts_with($phoneDigits, '8')) {
        $phoneDigits = '0'.$phoneDigits;
    }

    $data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phoneDigits !== '' ? $phoneDigits : null,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation,
    ];

    $validator = Validator::make($data, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:30'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ], [
        'name.required' => 'Nama Owner wajib diisi.',
        'email.required' => 'Email Owner wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Kata sandi wajib diisi.',
        'password.min' => 'Kata sandi minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return Command::FAILURE;
    }

    $otherOwnerExists = User::withTrashed()
        ->where('account_type', User::ACCOUNT_INTERNAL)
        ->where('role', User::ROLE_OWNER)
        ->where('email', '!=', $data['email'])
        ->exists();

    if ($otherOwnerExists) {
        $this->error('Akun Owner lain sudah tersedia. Gunakan akun Owner yang sudah ada.');

        return Command::FAILURE;
    }

    $duplicatePhone = $data['phone']
        ? User::withTrashed()
            ->where('phone', $data['phone'])
            ->where('email', '!=', $data['email'])
            ->exists()
        : false;

    if ($duplicatePhone) {
        $this->error('Nomor telepon sudah digunakan oleh akun lain.');

        return Command::FAILURE;
    }

    $user = User::withTrashed()->updateOrCreate(
        ['email' => $data['email']],
        [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'account_type' => User::ACCOUNT_INTERNAL,
            'role' => User::ROLE_OWNER,
            'is_active' => true,
            'deleted_at' => null,
        ],
    );

    $this->newLine();
    $this->info("Akun Owner berhasil disimpan untuk {$user->email}.");
    $this->line('Role: Owner');
    $this->line('Akses: seluruh fitur internal dan data sensitif.');
    $this->line('Gunakan email/nomor telepon dan kata sandi tersebut pada /admin/login.');

    return Command::SUCCESS;
})->purpose('Membuat akun Owner awal untuk login Admin');

Artisan::command('ettra:assign-owner {identifier?}', function (?string $identifier = null) {
    $identifier = trim((string) ($identifier ?: $this->ask('Email atau nomor telepon akun Owner yang sudah ada')));

    if ($identifier === '') {
        $this->error('Email atau nomor telepon wajib diisi.');

        return Command::FAILURE;
    }

    $query = User::withTrashed()
        ->where('account_type', User::ACCOUNT_INTERNAL);

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $query->whereRaw('LOWER(email) = ?', [Str::lower($identifier)]);
    } else {
        $phone = preg_replace('/\D+/', '', $identifier) ?? '';

        if (str_starts_with($phone, '62')) {
            $phone = '0'.substr($phone, 2);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '0'.$phone;
        }

        $query->where('phone', $phone);
    }

    $user = $query->first();

    if (! $user) {
        $this->error('Akun internal tidak ditemukan.');

        return Command::FAILURE;
    }

    $otherOwnerExists = User::withTrashed()
        ->where('account_type', User::ACCOUNT_INTERNAL)
        ->where('role', User::ROLE_OWNER)
        ->where('id', '!=', $user->id)
        ->exists();

    if ($otherOwnerExists) {
        $this->error('Akun Owner lain sudah tersedia. Hanya satu akun Owner utama yang digunakan pada tahap ini.');

        return Command::FAILURE;
    }

    $user->forceFill([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
        'deleted_at' => null,
    ])->save();

    $this->info("{$user->name} ({$user->email}) sekarang memiliki role Owner.");

    return Command::SUCCESS;
})->purpose('Menetapkan akun internal yang sudah ada sebagai Owner');

Artisan::command('ettra:create-admin', function () {
    $this->info('Membuat atau memperbarui akun Admin Ettra Signature.');
    $this->newLine();

    $name = trim((string) $this->ask('Nama Admin'));
    $email = Str::lower(trim((string) $this->ask('Email Admin')));
    $phoneInput = trim((string) $this->ask('Nomor telepon (opsional)', ''));
    $password = (string) $this->secret('Kata sandi');
    $passwordConfirmation = (string) $this->secret('Ulangi kata sandi');

    $phoneDigits = preg_replace('/\D+/', '', $phoneInput) ?? '';

    if (str_starts_with($phoneDigits, '62')) {
        $phoneDigits = '0'.substr($phoneDigits, 2);
    } elseif (str_starts_with($phoneDigits, '8')) {
        $phoneDigits = '0'.$phoneDigits;
    }

    $data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phoneDigits !== '' ? $phoneDigits : null,
        'password' => $password,
        'password_confirmation' => $passwordConfirmation,
    ];

    $validator = Validator::make($data, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['nullable', 'string', 'max:30'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ], [
        'name.required' => 'Nama Admin wajib diisi.',
        'email.required' => 'Email Admin wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'password.required' => 'Kata sandi wajib diisi.',
        'password.min' => 'Kata sandi minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

        return Command::FAILURE;
    }

    $existingUser = User::withTrashed()
        ->where('email', $data['email'])
        ->first();

    if ($existingUser?->isOwner()) {
        $this->error('Email tersebut digunakan oleh Owner dan tidak dapat diubah menjadi Admin.');

        return Command::FAILURE;
    }

    $duplicatePhone = $data['phone']
        ? User::withTrashed()
            ->where('phone', $data['phone'])
            ->where('email', '!=', $data['email'])
            ->exists()
        : false;

    if ($duplicatePhone) {
        $this->error('Nomor telepon sudah digunakan oleh akun lain.');

        return Command::FAILURE;
    }

    $user = User::withTrashed()->updateOrCreate(
        ['email' => $data['email']],
        [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'account_type' => User::ACCOUNT_INTERNAL,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'deleted_at' => null,
        ],
    );

    $this->newLine();
    $this->info("Akun Admin berhasil disimpan untuk {$user->email}.");
    $this->line('Role: Admin');
    $this->line('Akses: operasional penjualan, produk, persediaan, pembayaran, pengiriman, purchase request, arus kas, dan laporan non-sensitif.');
    $this->line('Tidak dapat mengakses vendor, User Management, harga beli/modal, margin, profit, atau persetujuan purchase request.');

    return Command::SUCCESS;
})->purpose('Membuat akun Admin operasional Ettra Signature');
